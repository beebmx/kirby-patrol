<?php

use Beebmx\KirbyPatrol\Facades\Patrol;
use Beebmx\KirbyPatrol\Facades\Pipeline;
use Kirby\Cms\Page;
use Kirby\Http\Response;

return [
    'page.render:before' => function (string $contentType, array $data, Page $page) {
        $response = Pipeline::send($data)
            ->through(Patrol::middleware())
            ->then(fn ($data) => $data);

        if ($response instanceof Response) {
            $response->send();
        }

        return $data;
    },
];
