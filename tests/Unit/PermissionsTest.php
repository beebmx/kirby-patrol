<?php

use Beebmx\KirbyPatrol\Content;
use Beebmx\KirbyPatrol\Permissions;
use Kirby\Cms\Pages;
use Kirby\Cms\Role;
use Kirby\Filesystem\Dir;

describe('basic', function () {
    beforeEach(function () {
        $this->permissions = new Permissions(App(), new Content(App()));
    });

    it('returns a pages collection', function () {
        expect($this->permissions->all())
            ->toBeInstanceOf(Pages::class);
    });

    it('returns an array', function () {
        expect($this->permissions->toArray())
            ->toBeArray();
    });

    it('returns a flat array with keys pages and permissions', function () {
        expect($this->permissions)
            ->toArray()
            ->toHaveKeys(['blog', 'blog/post-01', 'content', 'content/content-01'])
            ->not->toHaveKeys(['content/content-01/deep-01', 'content/content-01/deep-02']);
    });

    it('returns permissions for given role', function () {
        expect($this->permissions->for(Role::admin()))
            ->toBeArray()
            ->toBe($this->permissions->toArray());
    });
});

describe('advance', function () {
    beforeEach(function () {
        $this->permissions = new Permissions(App(), new Content(App(options: [
            'beebmx.kirby-patrol.depth' => 3,
        ])));
    });

    it('returns a flat array with keys pages and permissions for depth levels', function () {
        expect($this->permissions)
            ->toArray()
            ->toHaveKeys(['blog', 'blog/post-01', 'content', 'content/content-01', 'content/content-01/deep-01', 'content/content-01/deep-02']);
    });
});

afterAll(function () {
    Dir::remove(storage('patrol'));
});
