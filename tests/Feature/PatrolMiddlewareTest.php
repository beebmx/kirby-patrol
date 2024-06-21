<?php

use Beebmx\KirbyPatrol\Middleware\PatrolMiddleware;
use Beebmx\KirbyPatrol\Patrol;
use Kirby\Cms\Role;
use Kirby\Exception\ErrorPageException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;

describe('basic', function () {
    beforeEach(function () {
        $this->kirby = App(users: [[
            'email' => 'john@doe.co',
            'id' => 'john',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin',
        ], [
            'email' => 'jane@doe.co',
            'id' => 'jane',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'editor',
        ]]);

        $this->patrol = new Patrol($this->kirby);
        $this->admin = Role::admin();
        $this->editor = $this->patrol->roles()->get('editor');
        $this->patrol->store(role: $this->admin, permissions: ['blog' => true]);
        $this->patrol->store(role: $this->editor, permissions: ['blog' => false]);

        $this->data = [
            'kirby' => $this->kirby,
            'site' => $site = $this->kirby->site(),
            'pages' => $site->pages(),
            'page' => $this->kirby->page('blog'),
        ];
    });

    it('throws an error if role doesnt have access', function () {
        $this->kirby->impersonate('jane', function () {
            $middleware = new PatrolMiddleware;
            $middleware->handle($this->data, fn () => $this->data);
        });
    })->throws(ErrorPageException::class);

    it('wont throws an error if role have access', function () {
        $this->kirby->impersonate('john', function () {
            $middleware = new PatrolMiddleware;
            $middleware->handle($this->data, fn () => $this->data);
        });
    })->throwsNoExceptions();
});

describe('redirect', function () {
    beforeEach(function () {
        $this->kirby = App(
            options: [
                'beebmx.kirby-patrol.permissions.redirect' => 'home',
            ], users: [[
                'email' => 'john@doe.co',
                'id' => 'john',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'editor',
            ]]
        );

        $this->patrol = new Patrol($this->kirby);
        $this->editor = $this->patrol->roles()->get('editor');
        $this->patrol->store(role: $this->editor, permissions: ['blog' => false]);

        $this->data = [
            'kirby' => $this->kirby,
            'site' => $site = $this->kirby->site(),
            'pages' => $site->pages(),
            'page' => $this->kirby->page('blog'),
        ];
    });

    it('wont throws an error if redirect is set', function () {
        $this->kirby->impersonate('john', function () {
            $middleware = new PatrolMiddleware;
            $middleware->handle($this->data, fn () => $this->data);
        });
    })->throwsNoExceptions();

    it('redirects when permissions.redirect is set', function () {
        $this->kirby->impersonate('john', function () {
            $middleware = new PatrolMiddleware;
            $redirect = $middleware->handle($this->data, fn () => $this->data);

            expect($redirect)
                ->toBeInstanceOf(Response::class);
        });
    });

});

describe('disabled', function () {
    beforeEach(function () {
        $this->kirby = App(
            options: [
                'beebmx.kirby-patrol.permissions.enabled' => false,
            ], users: [[
                'email' => 'john@doe.co',
                'id' => 'john',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'editor',
            ]]
        );

        $this->patrol = new Patrol($this->kirby);
        $this->editor = $this->patrol->roles()->get('editor');
        $this->patrol->store(role: $this->editor, permissions: ['blog' => false]);

        $this->data = [
            'kirby' => $this->kirby,
            'site' => $site = $this->kirby->site(),
            'pages' => $site->pages(),
            'page' => $this->kirby->page('blog'),
        ];
    });

    it('wont add PatrolMiddleware if permissions are disabled', function () {
        expect($this->patrol->middleware())
            ->toBeArray()
            ->toBeEmpty();
    });
});

describe('guest', function () {
    beforeEach(function () {
        $this->kirby = App();

        $this->patrol = new Patrol($this->kirby);
        $this->admin = Role::admin();
        $this->editor = $this->patrol->roles()->get('editor');
        $this->patrol->store(role: $this->admin, permissions: ['blog' => true]);
        $this->patrol->store(role: $this->editor, permissions: ['blog' => false]);

        $this->data = [
            'kirby' => $this->kirby,
            'site' => $site = $this->kirby->site(),
            'pages' => $site->pages(),
            'page' => $this->kirby->page('home'),
        ];
    });

    it('wont throws if a guest access to a none validating page', function () {
        $middleware = new PatrolMiddleware;
        $middleware->handle($this->data, fn () => $this->data);
    })->throwsNoExceptions();

    it('wont throws if a guest access to an invalid page', function () {
        $this->data['page'] = $this->kirby->page('invalid');

        $middleware = new PatrolMiddleware;
        $middleware->handle($this->data, fn () => $this->data);
    })->throwsNoExceptions();
});

afterAll(function () {
    Dir::remove(storage('patrol'));
});
