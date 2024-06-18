<?php

use Beebmx\KirbyPatrol\Facades\Translation;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Facade;

it('extends facade', function () {
    expect(Translation::class)
        ->toExtend(Facade::class);
});

it('returns all languages available', function () {
    $languages = Dir::read(dirname(__DIR__, 2).'/extensions/languages');

    expect(Translation::all())
        ->toHaveCount(count($languages));
});

it('contains locale as key', function () {
    expect(Translation::all())
        ->toHaveKeys(['en', 'es']);
});

it('can return an specific locale', function () {
    expect(Translation::locale('es'))
        ->toBeArray()
        ->toHaveKey('beebmx.kirby-patrol.name', 'Patrulla');
});

test('if a locale doesnt have a valid key, it will return an english as default', function () {
    expect(Translation::locale('invalid'))
        ->toBeArray()
        ->toHaveKey('beebmx.kirby-patrol.name', 'Patrol');
});

it('returns an specific key in all localizations available', function () {
    expect(Translation::key('beebmx.kirby-patrol.name'))
        ->toBeArray()
        ->toHaveKey('en', 'Patrol')
        ->toHaveKey('es', 'Patrulla');
});
