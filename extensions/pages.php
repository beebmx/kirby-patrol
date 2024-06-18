<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;

return [
    'patrol' => function ($access = true) {
        $kirby = Kirby::instance();

        return $this->filter(
            fn (Page $page) => in_array($page->id(), $kirby->user()->patrol($access))
        );
    },
];
