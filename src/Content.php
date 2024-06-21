<?php

namespace Beebmx\KirbyPatrol;

use Beebmx\KirbyPatrol\Concerns\UseIterators;
use Closure;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;

class Content
{
    use UseIterators;

    protected ?Pages $content;

    protected int $depth;

    protected string $sort;

    protected string $direction;

    public function __construct(protected Kirby $kirby)
    {
        $this->setup();
    }

    protected function setup(): void
    {
        $this
            ->setDepth()
            ->setSort()
            ->setContent();
    }

    public function setDepth(): static
    {
        $this->depth = $this->kirby->option('beebmx.kirby-patrol.content.depth', 2);

        return $this;
    }

    public function setSort(): static
    {
        $this->sort = $this->kirby->option('beebmx.kirby-patrol.content.sort', 'title');
        $this->direction = $this->kirby->option('beebmx.kirby-patrol.content.direction', 'asc');

        return $this;
    }

    protected function setContent(): static
    {
        $closure = $this->kirby->option('beebmx.kirby-patrol.content.query');

        $this->content = $closure instanceof Closure
            ? $closure($this->kirby->site(), $this->kirby->site()->pages(), $this->kirby)
            : $this->kirby->site()->children()->published()->not('home', 'error')->sortBy($this->sort, $this->direction);

        return $this;
    }

    public function all(): Pages
    {
        return $this->content ?? Pages::factory([]);
    }

    public function toArray(): array
    {
        return $this->getContent($this->content->clone());
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    protected function getContent(Site|Pages|Page|array $pages, int $depth = 1): array
    {
        return $pages->map(
            fn (Page|array $page) => is_array($page)
                ? $page
                : array_merge([
                    ...$page->toArray(),
                    'children' => $depth < $this->depth ? $this->getContent($page->children()->sortBy($this->sort, $this->direction), $depth + 1) : [],
                ])
        )->data();
    }
}
