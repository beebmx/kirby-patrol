<?php

use Beebmx\KirbyPatrol\Facades\Patrol;
use Kirby\Cms\Page;

return [
    'can' => function (string|Page $page) {
        return Patrol::can($this->role(), $page);
    },
    'patrol' => function ($access = true) {
        return Patrol::filterFor($this->role(), $access);
    },
];
