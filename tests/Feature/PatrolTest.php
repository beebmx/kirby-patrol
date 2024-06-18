<?php

use Beebmx\KirbyPatrol\Controllers\PermissionController;
use Kirby\Filesystem\Dir;

beforeEach(function () {
    $this->kirby = App(request: [
        'method' => 'POST',
        'body' => ['permissions' => [
            'blog' => false,
            'blog/post-01' => false,
            'blog/post-02' => false,
            'blog/post-03' => false,
            'content' => true,
            'content/content-01' => true,
            'content/content-02' => true,
            'content/content-03' => true,
            'content/content-04' => true,
            'content/extra' => true,
            'home' => true,
        ]],
    ]);
});

it('saves data for given role', function () {
    expect((new PermissionController($this->kirby))('admin'))
        ->toBeArray()
        ->toHaveKey('saved', true);
});

afterAll(function () {
    Dir::remove(storage('patrol'));
});
