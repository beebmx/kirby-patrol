<?php

use Beebmx\KirbyPatrol\Facades\Patrol;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Permissions;
use Kirby\Toolkit\Str;

return [
    'patrol' => function (Kirby $kirby): array {
        if (! $kirby->user()) {
            return [];
        }

        $access = array_key_exists('beebmx.kirby-patrol', $kirby->user()->role()->permissions()->toArray())
            ? $kirby->user()->role()->permissions()->for('beebmx.kirby-patrol', 'access')
            : (new Permissions)->for('beebmx.kirby-patrol', 'access');

        if (! $access) {
            return [];
        }

        $version = Str::split(Kirby::instance()->version(), '.')[0];

        $component = match ($version) {
            '4' => 'k-patrol-view-4',
            '5' => 'k-patrol-view-5',
            default => 'k-patrol-view',
        };

        return [
            'label' => $kirby->option('beebmx.kirby-patrol.name', 'Patrol'),
            'icon' => Patrol::icon(
                $kirby->option('beebmx.kirby-patrol.icon', 'shield')
            ),
            'menu' => $kirby->option('beebmx.kirby-patrol.enabled', false),
            'link' => 'patrol',
            'views' => [[
                'pattern' => 'patrol',
                'action' => function () use ($component) {
                    $kirby = Kirby::instance();

                    $role = $kirby->roles()->get(
                        $kirby->request()->get('role', 'admin')
                    );

                    return [
                        'component' => $component,
                        'title' => $kirby->option('beebmx.kirby-patrol.name', 'Patrol'),
                        'props' => [
                            'columns' => Patrol::columns(
                                $kirby->option('beebmx.kirby-patrol.content.columns', 2)
                            ),
                            'content' => Patrol::content()->toArray(),
                            'patrol' => Patrol::for($role),
                            'role' => $role->toArray(),
                            'roles' => $kirby->roles()->toArray(),
                        ],
                    ];
                },
            ]],
        ];
    },
];
