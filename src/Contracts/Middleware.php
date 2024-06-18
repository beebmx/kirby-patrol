<?php

namespace Beebmx\KirbyPatrol\Contracts;

use Closure;

interface Middleware
{
    public function handle(array $data, Closure $next);
}
