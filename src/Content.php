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

    public function __construct(protected Kirby $kirby)
    {
        $this->setup();
    }

    protected function setup(): void
    {
        $this
            ->setDeep()
            ->setContent();
    }

    public function setDeep(): static
    {
        $this->depth = $this->kirby->option('beebmx.kirby-patrol.depth', 2);

        return $this;
    }

    protected function setContent(): static
    {
        $closure = $this->kirby->option('beebmx.kirby-patrol.query');

        $this->content = $closure instanceof Closure
            ? $closure($this->kirby->site(), $this->kirby->site()->pages(), $this->kirby)
            : $this->kirby->site()->children()->published()->not('home', 'error')->sortBy('title');

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
                    'children' => $depth < $this->depth ? $this->getContent($page->children()->sortBy('title'), $depth + 1) : [],
                ])
        )->data();
    }
}
