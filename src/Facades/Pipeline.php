<?php

namespace Beebmx\KirbyPatrol\Facades;

use Beebmx\KirbyPatrol\Support\Pipeline as P;
use Kirby\Toolkit\Facade;

class Pipeline extends Facade
{
    protected static P $singleton;

    public static function instance(): P
    {
        if (! isset(self::$singleton)) {
            self::$singleton = new P;
        }

        return self::$singleton;
    }
}
