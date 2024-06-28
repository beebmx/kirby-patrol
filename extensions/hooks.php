<?php

use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyPatrol\Facades\Patrol;
use Beebmx\KirbyPatrol\Facades\Pipeline;
use Beebmx\KirbyPatrol\Middleware\PatrolMiddlewareGroup;
use Kirby\Cms\Page;
use Kirby\Http\Response;

return [
    'system.loadPlugins:after' => function () {
        Middleware::instance()->addClassToGroup(
            PatrolMiddlewareGroup::class
        );
    },
];
