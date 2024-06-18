<?php

namespace Beebmx\KirbyPatrol;

use Beebmx\KirbyPatrol\Concerns\UseIterators;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

class Translation
{
    use UseIterators;

    protected array $languages;

    public function __construct()
    {
        $path = dirname(__DIR__).'/extensions/languages';
        $this->languages = static::mapWithKeys(Dir::index($path),
            fn ($locale) => [substr($locale, 0, -4) => Data::read($path.'/'.$locale)]
        );
    }

    public function locale(string $locale): array
    {
        if (array_key_exists($locale, $this->languages)) {
            return $this->languages[$locale];
        }

        return $this->languages['en'];
    }

    public function all(): array
    {
        return $this->languages;
    }

    public function key(string $key): array
    {
        return static::map($this->languages,
            fn ($locale) => $locale[$key]
        );
    }
}
