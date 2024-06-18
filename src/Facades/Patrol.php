<?php

namespace Beebmx\KirbyPatrol\Facades;

use Beebmx\KirbyPatrol\Patrol as P;
use Kirby\Toolkit\Facade;

class Patrol extends Facade
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
