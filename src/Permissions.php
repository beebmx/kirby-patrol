<?php

namespace Beebmx\KirbyPatrol;

use Beebmx\KirbyPatrol\Concerns\UseIterators;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Collection;
use Kirby\Cms\Pages;
use Kirby\Cms\Role;

class Permissions
{
    use UseIterators;

    protected bool $default;

    protected Pages $pages;

    protected $data;

    public function __construct(protected Kirby $kirby, protected Content $content)
    {
        $this->setup();
    }

    protected function setup(): void
    {
        $this
            ->setDefault()
            ->setPages()
            ->setPermissions();
    }

    protected function setDefault(): static
    {
        $this->default = $this->kirby->option('beebmx.kirby-patrol.permissions.default', true);

        return $this;
    }

    protected function setPages(): static
    {
        $this->pages = $this->flatten(
            Pages::factory($this->content->toArray())
        );

        return $this;
    }

    protected function setPermissions(): static
    {
        $this->data = static::mapWithKeys($this->pages->toArray(),
            fn ($page, $key) => [$key => $this->default]
        );

        return $this;
    }

    public function all(): Collection
    {
        return $this->pages;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function for(Role $role, array $permissions = []): array
    {
        return array_merge(
            $this->toArray(),
            $permissions
        );
    }

    protected function flatten(Pages $content): Pages
    {
        $pages = new Pages([]);

        foreach ($content as $pKey => $page) {
            $pages->data[$pKey] = $page;

            if ($page->children()->count() > 0) {
                $childrens = $this->flatten($page->children());

                foreach ($childrens as $cKey => $children) {
                    $pages->data[$cKey] = $children;
                }
            }

        }

        return $pages;
    }
}
