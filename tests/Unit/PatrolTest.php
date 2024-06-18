<?php

use Beebmx\KirbyPatrol\Patrol;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Role;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

describe('basic', function () {
    beforeEach(function () {
        $this->patrol = new Patrol(App());
    });

    it('returns the current path of patrol configurations', function () {
        expect($this->patrol->path())
            ->toBe(storage('patrol'));
    });

    it('creates a patrol directory if it doesnt exist', function () {
        expect(Dir::exists($this->patrol->path()))
            ->toBeTrue();
    });

    it('creates files for each role available with panel enabled', function () {
        expect(Dir::read($this->patrol->path()))
            ->toContain('admin.txt')
            ->toContain('editor.txt')
            ->toContain('restricted.txt');
    });

    it('return a path file for given role', function () {
        expect($this->patrol->pathFor(Role::admin()))
            ->toEndWith('admin.txt')
            ->toStartWith($this->patrol->path());
    });
});

describe('store', function () {
    beforeEach(function () {
        $this->kirby = App();
        $this->patrol = new Patrol($this->kirby);
        $this->patrol->store(Role::admin(), permissions: [
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
        ]);
    });

    it('store data for given role', function () {
        $permissions = Data::read($this->patrol->pathFor(Role::admin()))['permissions'];

        expect($permissions)
            ->toBeJson()
            ->and(json_decode($permissions))
            ->toHaveKey('blog', false)
            ->toHaveKey('blog/post-01', false)
            ->toHaveKey('blog/post-02', false)
            ->toHaveKey('blog/post-03', false)
            ->toHaveKey('content', true)
            ->toHaveKey('content/content-01', true)
            ->toHaveKey('content/content-02', true)
            ->toHaveKey('content/content-03', true)
            ->toHaveKey('content/content-04', true)
            ->toHaveKey('content/extra', true)
            ->toHaveKey('home', true);
    });

    it('can retrive permissions for given role', function () {
        expect($this->patrol->for(Role::admin()))
            ->toBeArray()
            ->toHaveKeys(['blog', 'blog/post-01', 'content', 'content/content-01', 'content/extra', 'home']);
    });

    it('returns the default value if is not set', function () {
        $editor = $this->patrol->roles()->get('editor');

        expect($this->patrol->for($editor))
            ->toBeArray()
            ->not->toBeEmpty()
            ->toHaveKey('blog', true)->toHaveKey('content', true)
            ->toHaveKey('content', true)
            ->toHaveKey('content/content-01', true);
    });
});

describe('access', function () {
    beforeEach(function () {
        $this->patrol = new Patrol(App());

        $this->admin = Role::admin();
        $this->editor = $this->patrol->roles()->get('editor');

        $this->patrol->store(role: $this->admin, permissions: ['blog' => true]);
        $this->patrol->store(role: $this->editor, permissions: ['blog' => false]);
    });

    it('return null if a page doesnt need validation', function () {
        expect($this->patrol->can(role: $this->admin, page: 'invalid-page'))
            ->toBeNull();
    });

    it('returns access for given role', function () {
        expect($this->patrol->can(role: $this->admin, page: 'blog'))
            ->toBeTrue()
            ->and($this->patrol->can(role: $this->editor, page: 'blog'))
            ->toBeFalse();
    });

    it('allows page object', function () {
        $page = Kirby::instance()->site()->pages()->get('blog');

        expect($this->patrol->can(role: $this->editor, page: $page))
            ->toBeFalse();
    });

    it('returns if page as string exists in the permission collection', function () {
        expect($this->patrol->exists('non-existent-page'))
            ->toBeFalse();
    });

    it('returns if page as object exists in the permission collection', function () {
        $page = Kirby::instance()->site()->pages()->get('blog');

        expect($this->patrol->exists($page))
            ->toBeTrue();
    });

    it('returns if no page exists in the permission collection', function () {
        expect($this->patrol->exists(null))
            ->toBeFalse();
    });
});

describe('advance', function () {
    beforeEach(function () {
        $this->kirby = App(['patrol' => fixtures('demo/patrol')]);
        $this->patrol = new Patrol($this->kirby);
    });

    it('returns the root path if patrol root is defined', function () {
        expect($this->patrol->path())
            ->toBe($this->kirby->roots()->patrol());
    });
});

describe('filters', function () {
    beforeEach(function () {
        $this->kirby = App();
        $this->patrol = new Patrol($this->kirby);
        $this->patrol->store(Role::admin(), permissions: [
            'blog' => false,
            'blog/post-01' => false,
            'blog/post-02' => false,
            'blog/post-03' => false,
        ]);
    });

    it('returns pages with access', function () {
        expect($this->patrol->filterFor(Role::admin()))
            ->toBeArray()
            ->toContain('content', 'content/content-01', 'content/content-02', 'content/content-03', 'content/content-04', 'content/extra')
            ->not->toContain('blog', 'blog/post-01', 'blog/post-02', 'blog/post-03');
    });

    it('returns pages without access', function () {
        expect($this->patrol->filterFor(role: Role::admin(), access: false))
            ->toBeArray()
            ->toContain('blog', 'blog/post-01', 'blog/post-02', 'blog/post-03')
            ->not->toContain('content', 'content/content-01', 'content/content-02', 'content/content-03', 'content/content-04', 'content/extra');
    });
});

describe('statics', function () {
    it('returns default icon', function () {
        expect(Patrol::icon())
            ->toBe('patrol-keyhole');
    });

    it('returns a specific icon', function () {
        expect(Patrol::icon('siren'))
            ->toBe('patrol-siren');
    });

    it('returns default columns', function () {
        expect(Patrol::columns())
            ->toBe(4);
    });

    it('returns a specific column with string', function () {
        expect(Patrol::columns('1/2'))
            ->toBe(2);
    });
});

afterAll(function () {
    Dir::remove(storage('patrol'));
    Dir::remove(fixtures('demo/patrol'));
});
