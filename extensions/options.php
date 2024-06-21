<?php

return [
    'enabled' => true,
    'icon' => 'keyhole',
    'name' => 'Patrol',
    'content' => [
        'columns' => 4,
        'depth' => 2,
        'direction' => 'asc',
        'query' => null,
        'sort' => 'title',
    ],
    'permissions' => [
        'default' => true,
        'enabled' => true,
        'guest' => false,
        'middleware' => [],
        'redirect' => null,
    ],
];
