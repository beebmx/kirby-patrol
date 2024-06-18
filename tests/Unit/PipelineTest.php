<?php

use Beebmx\KirbyPatrol\Facades\Pipeline;
use Tests\Fixtures\pipes\PipelineTestOne;
use Tests\Fixtures\pipes\PipelineTestTwo;

it('return pipe results with classes', function () {
    $string = Pipeline::send('Pipes:')
        ->through([
            PipelineTestOne::class,
            PipelineTestTwo::class,
        ])->then(fn ($string) => $string);

    expect($string)
        ->toBe('Pipes: One Two');
});

it('return pipe results with closures', function () {
    $string = Pipeline::send('Pipes:')
        ->through([
            function ($string, $next) {
                $string = "$string One";

                return $next($string);
            },
            function ($string, $next) {
                $string = "$string Two";

                return $next($string);
            },

        ])->then(fn ($string) => $string);

    expect($string)
        ->toBe('Pipes: One Two');
});

it('return pipe results with classes and closures', function () {
    $string = Pipeline::send('Pipes:')
        ->through([
            PipelineTestOne::class,
            function ($string, $next) {
                $string = "$string Two";

                return $next($string);
            },
        ])->then(fn ($string) => $string);

    expect($string)
        ->toBe('Pipes: One Two');
});

it('return pipe results with classes via other method', function () {
    $string = Pipeline::send('Pipe with')
        ->via('other')
        ->through([
            PipelineTestOne::class,
            PipelineTestTwo::class,
        ])->then(fn ($string) => $string);

    expect($string)
        ->toBe('Pipe with other method');
});

it('return pipe results with classes via other method and closure', function () {
    $string = Pipeline::send('Pipe with')
        ->via('other')
        ->through([
            PipelineTestOne::class,
            function ($string, $next) {
                $string = "$string method";

                return $next($string);
            },
        ])->then(fn ($string) => $string);

    expect($string)
        ->toBe('Pipe with other method');
});

it('return the pipe', function () {
    $string = Pipeline::send('Pipe')
        ->through([
            function ($string, $next) {
                return $next($string);
            },
            function ($string, $next) {
                return $next($string);
            },
        ])->execute();

    expect($string)
        ->toBe('Pipe');
});
