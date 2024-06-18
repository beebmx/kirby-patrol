<?php

namespace Beebmx\KirbyPatrol\Facades;

use Beebmx\KirbyPatrol\Translation as T;
use Kirby\Toolkit\Facade;

class Translation extends Facade
{
    protected static T $singleton;

    public static function instance(): T
    {
        if (! isset(self::$singleton)) {
            self::$singleton = new T;
        }

        return self::$singleton;
    }
}
