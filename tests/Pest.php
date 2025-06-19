<?php

use Kirby\Cms\App as Kirby;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->uses(Tests\TestCase::class)->in('Feature', 'Unit');


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function App(array $roots = [], array $options = [], ?array $request = null, ?array $users = null): Kirby
{
    Kirby::$enableWhoops = false;

    return new Kirby([
        'roots' => array_merge([
            'index' => '/dev/null',
            'base' => $base = dirname(__DIR__),
            'tests' => $tests = $base.'/tests',
            'fixtures' => $fixtures = $tests.'/Fixtures',
            'site' => $site = $fixtures.'/site',
            'content' => $fixtures.'/content',
            'storage' => $storage = $fixtures.'/storage',
            'blueprints' => $site.'/blueprints',
            'accounts' => $storage.'/accounts',
        ], $roots),
        'options' => $options,
        'request' => $request,
        'users' => $users,
    ]);
}

function RenderApp(array $roots = []): Kirby
{
    Kirby::$enableWhoops = false;

    $kirby = App($roots);
    echo $kirby->render();

    return $kirby;
}

function fixtures(string $path): string
{
    return dirname(__DIR__).'/tests/Fixtures/'.$path;
}

function storage(string $path): string
{
    return fixtures('storage/'.$path);
}
