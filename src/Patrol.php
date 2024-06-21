<?php

namespace Beebmx\KirbyPatrol;

use Beebmx\KirbyPatrol\Concerns\UseIterators;
use Beebmx\KirbyPatrol\Middleware\PatrolMiddleware;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use Kirby\Cms\Role;
use Kirby\Cms\Roles;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class Patrol
{
    use UseIterators;

    protected Kirby $kirby;

    protected string $path;

    protected Roles $roles;

    protected Content $content;

    protected Permissions $permissions;

    public function __construct(?Kirby $kirby = null)
    {
        $this->kirby = $kirby ?? Kirby::instance();
        $this->setup();
    }

    protected function setup(): static
    {
        $this
            ->setPath()
            ->setRoles()
            ->setContent()
            ->setPermissions();

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function roles(): Roles
    {
        return $this->roles;
    }

    public function content(): Content
    {
        return $this->content;
    }

    public function permissions(): Permissions
    {
        return $this->permissions;
    }

    public function pathFor(Role $role): string
    {
        return $this->path.'/'.$role->name().'.txt';
    }

    public function store(Role $role, array $permissions): bool
    {
        return Data::write($this->pathFor($role), ['permissions' => json_encode($permissions)]);
    }

    public function for(Role $role): array
    {
        $data = Data::read($this->pathFor($role));

        return array_merge(
            $this->permissions->for($role),
            array_key_exists('permissions', $data)
                ? json_decode($data['permissions'], true)
                : [],
        );
    }

    public function filterFor(Role $role, bool $access = true): array
    {
        return array_keys(
            array_filter($this->for($role), fn ($item) => $item === $access)
        );
    }

    public function can(Role $role, Page|string $page): ?bool
    {
        $page = $page instanceof Page
            ? $page->id()
            : $page;

        $permissions = $this->for($role);

        if (! array_key_exists($page, $permissions)) {
            return null;
        }

        return $permissions[$page];
    }

    public function exists(Page|string|null $page): bool
    {
        $page = $page instanceof Page
            ? $page->id()
            : $page;

        if (is_null($page)) {
            return false;
        }

        return in_array($page, array_keys($this->permissions->toArray()));
    }

    public function middleware(): array
    {
        return array_merge(
            $this->kirby->option('beebmx.kirby-patrol.permissions.enabled', true)
                ? [PatrolMiddleware::class]
                : []
        , $this->kirby->option('beebmx.kirby-patrol.permissions.middleware', []));
    }

    protected function setPath(): static
    {
        $this->path = $this->kirby->roots()->patrol()
            ? $this->kirby->roots()->patrol()
            : dirname($this->kirby->roots()->accounts()).'/patrol';

        if (! Dir::exists($this->path)) {
            Dir::make($this->path);
        }

        return $this;
    }

    protected function setRoles(): static
    {
        $this->roles = $this->kirby->roles();

        foreach ($this->roles->data() as $role) {
            if (! F::exists($file = $this->path.'/'.$role->id().'.txt')) {
                Data::write($file);
            }
        }

        return $this;
    }

    protected function setContent(): static
    {
        $this->content = new Content($this->kirby);

        return $this;
    }

    protected function setPermissions(): static
    {
        $this->permissions = new Permissions($this->kirby, $this->content);

        return $this;
    }

    public static function icon(string $icon = 'keyhole'): string
    {
        return match ($icon) {
            'user' => 'patrol-user',
            'shield' => 'patrol-shield',
            'flash' => 'patrol-flash',
            'star' => 'patrol-star',
            'siren' => 'patrol-siren',
            'police' => 'patrol-police',
            default => 'patrol-keyhole'
        };
    }

    public static function columns(int|string $columns = 4): int
    {
        return match ($columns) {
            1, '1/1' => 1,
            2, '1/2', '2/4' => 2,
            3, '1/3', '2/6', '4/12' => 3,
            5, '1/5' => 5,
            6, '1/6' => 6,
            default => 4
        };
    }
}
