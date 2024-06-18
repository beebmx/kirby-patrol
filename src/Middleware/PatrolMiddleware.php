<?php

namespace Beebmx\KirbyPatrol\Middleware;

use Beebmx\KirbyPatrol\Facades\Patrol;
use Closure;
use Kirby\Cms\App as Kirby;
use Kirby\Exception\ErrorPageException;
use Kirby\Http\Response;

class PatrolMiddleware extends Middleware
{
    public function handle(array $data, Closure $next)
    {
        $kirby = Kirby::instance();

        if ($kirby->option('beebmx.kirby-patrol.permissions.enabled', true) && Patrol::exists(page: $data['page'])) {
            if (Patrol::can(role: $kirby->user()->role(), page: $data['page']->id()) === false) {
                return $kirby->option('beebmx.kirby-patrol.permissions.redirect') !== null
                    ? Response::redirect($kirby->option('beebmx.kirby-patrol.permissions.redirect'), 401)
                    : throw new ErrorPageException([
                        'fallback' => 'Unauthorized',
                        'httpCode' => 401,
                    ]);
            }
        }

        return $next($data);
    }
}
