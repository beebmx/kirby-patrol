<?php

use Beebmx\KirbyMiddleware\Facades\Middleware;
use Beebmx\KirbyPatrol\Middleware\PatrolMiddlewareGroup;

return [
    'system.loadPlugins:after' => function () {
        Middleware::addClassToGroup(
            PatrolMiddlewareGroup::class
        );
    },
];
