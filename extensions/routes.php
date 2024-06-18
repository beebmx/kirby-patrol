<?php

use Beebmx\KirbyPatrol\Controllers\PermissionController;

return [
    [
        'pattern' => 'patrol/permission/(:any)',
        'method' => 'POST',
        'action' => fn ($role) => (new PermissionController($this->kirby()))($role),
    ],
];
