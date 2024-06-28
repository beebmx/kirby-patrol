<?php

namespace Beebmx\KirbyPatrol\Middleware;

use Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup;
use Kirby\Cms\App;

class PatrolMiddlewareGroup extends MiddlewareGroup
{
    public string|array $routes = '(:all)';

    public array $group = [
        \Beebmx\KirbyPatrol\Middleware\PatrolMiddleware::class,
    ];

    public function __construct()
    {
        $this->group = App::instance()->option('beebmx.kirby-patrol.permissions.enabled', true)
            ? array_merge($this->group, App::instance()->option('beebmx.kirby-patrol.permissions.middleware', []))
            : [];
    }
}
