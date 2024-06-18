<?php

use Beebmx\KirbyPatrol\Content;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;

describe('basic', function () {
    beforeEach(function () {
        $this->content = new Content(App());
    });

    it('exclude home page from default content', function () {
        expect($this->content->all()->toArray())
            ->not->toHaveKey('home');
    });

    it('returns all pages published', function () {
        expect($this->content->all()->toArray())
            ->toHaveCount(2)
            ->toHaveKey('content.content.title', 'Content')
            ->toHaveKey('blog.content.title', 'Blog');
    });

    it('returns content as array', function () {
        expect($this->content->toArray())
            ->toBeArray()
            ->toHaveKey('content.content.title', 'Content')
            ->toHaveKey('content.children.content/content-01.content.title', 'Content 01')
            ->toHaveKey('content.children.content/content-02.content.title', 'Content 02')
            ->toHaveKey('content.children.content/content-03.content.title', 'Content 03')
            ->toHaveKey('content.children.content/content-04.content.title', 'Content 04')
            ->toHaveKey('content.children.content/extra.content.title', 'Extra')
            ->toHaveKey('content.content.title', 'Content')
            ->toHaveKey('blog.content.title', 'Blog')
            ->toHaveKey('blog.children.blog/post-01.content.title', 'Post')
            ->toHaveKey('blog.children.blog/post-02.content.title', 'Post')
            ->toHaveKey('blog.children.blog/post-03.content.title', 'Post')
            ->not->toHaveKey('content.children.content/content-01.children.content/content-01/deep-01.content.title', 'Deep 01')
            ->not->toHaveKey('content.children.content/content-01.children.content/content-01/deep-02.content.title', 'Deep 02')
            ->not->toHaveKey('extra');
    });

    it('returns content as json', function () {
        expect($this->content->toJson())
            ->toBeString()
            ->toBeJson();
    });
});

describe('advance', function () {
    it('returns a specific query collection', function () {
        $content = new Content(App(options: [
            'beebmx.kirby-patrol.query' => function (Site $site, Pages $pages, Kirby $kirby): Pages {
                return $site->find('content')->children()->listed();
            },
        ]));

        expect($content->all()->toArray())
            ->toHaveCount(4)
            ->toHaveKey('content/content-01.content.title', 'Content 01')
            ->toHaveKey('content/content-02.content.title', 'Content 02')
            ->toHaveKey('content/content-03.content.title', 'Content 03')
            ->toHaveKey('content/content-04.content.title', 'Content 04')
            ->not->toHaveKey('content/extra');
    });

    it('can update the depth limit', function () {
        $content = new Content(App(options: [
            'beebmx.kirby-patrol.depth' => 3,
        ]));

        expect($content->toArray())
            ->toBeArray()
            ->toHaveKey('content.content.title', 'Content')
            ->toHaveKey('content.children.content/content-01.content.title', 'Content 01')
            ->toHaveKey('content.children.content/content-01.children.content/content-01/deep-01.content.title', 'Deep 01')
            ->toHaveKey('content.children.content/content-01.children.content/content-01/deep-02.content.title', 'Deep 02')
            ->toHaveKey('content.children.content/content-02.content.title', 'Content 02')
            ->toHaveKey('content.children.content/content-03.content.title', 'Content 03')
            ->toHaveKey('content.children.content/content-04.content.title', 'Content 04')
            ->toHaveKey('content.children.content/extra.content.title', 'Extra')
            ->toHaveKey('content.content.title', 'Content')
            ->toHaveKey('blog.content.title', 'Blog')
            ->toHaveKey('blog.children.blog/post-01.content.title', 'Post')
            ->toHaveKey('blog.children.blog/post-02.content.title', 'Post')
            ->toHaveKey('blog.children.blog/post-03.content.title', 'Post')
            ->not->toHaveKey('extra');
    });
});

describe('invalid data', function () {
    it('returns pages even with invalid data', function () {
        $content = new Content(App(options: [
            'beebmx.kirby-patrol.query' => function (Site $site, Pages $pages, Kirby $kirby) {
                return $site->find('invalid');
            },
        ]));

        expect($content->all())
            ->toBeInstanceOf(Pages::class)
            ->toArray()
            ->toHaveCount(0);
    });
});
