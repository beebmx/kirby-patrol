<?php

namespace Beebmx\KirbyPatrol\Concerns;

use ArgumentCountError;
use Kirby\Cms\Collection;

trait UseIterators
{
    public static function map(array $array, callable $callback): array
    {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }

    public static function mapWithKeys(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    public static function mapRecursive(array $array, callable $callback): array
    {
        array_walk_recursive($array, function (&$value) use ($callback) {
            $value = $callback($value);
        });

        return $array;
    }

    public static function collapse($array): array
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->toArray();
            } elseif (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }
}
