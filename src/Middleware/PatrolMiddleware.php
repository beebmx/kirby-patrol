<?php

namespace Beebmx\KirbyPatrol\Middleware;

use Beebmx\KirbyMiddleware\Request;
use Beebmx\KirbyPatrol\Facades\Patrol;
use Closure;
use Kirby\Cms\App;
use Kirby\Exception\ErrorPageException;
use Kirby\Http\Response;

class PatrolMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $kirby = App::instance();

        $page = $kirby->site()->page(
            $request->path()->toString() ?: 'home'
        );

        if (Patrol::exists(page: $page) && Patrol::can(role: $request->user()->role(), page: $page->id()) === false) {
            return $kirby->option('beebmx.kirby-patrol.permissions.redirect') !== null
                ? Response::redirect($kirby->option('beebmx.kirby-patrol.permissions.redirect'), 401)
                : throw new ErrorPageException([
                    'fallback' => 'Unauthorized',
                    'httpCode' => 401,
                ]);

        }

        return $next($request);
    }
}
