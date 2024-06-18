<?php

use Beebmx\KirbyPatrol\Facades\Translation;
use Kirby\Cms\App as Kirby;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('beebmx/kirby-patrol', [
    'api' => [
        'routes' => require_once __DIR__.'/extensions/routes.php',
    ],
    'areas' => require_once __DIR__.'/extensions/areas.php',
    'hooks' => require_once __DIR__.'/extensions/hooks.php',
    'options' => require_once __DIR__.'/extensions/options.php',
    'pagesMethods' => require_once __DIR__.'/extensions/pages.php',
    'permissions' => require_once __DIR__.'/extensions/permissions.php',
    'translations' => Translation::all(),
    'userMethods' => require_once __DIR__.'/extensions/user.php',
]);
