<?php

namespace Beebmx\KirbyPatrol\Controllers;

use Kirby\Cms\App as Kirby;

class Controller
{
    public function __construct(protected Kirby $kirby) {}
}
