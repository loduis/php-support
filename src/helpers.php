<?php

namespace Php;

if (!function_exists('array_reduce')) {
    function array_reduce(array $array, callable $callback, $initial = null)
    {
        $acc = $initial;
        foreach ($array as $key => $val) {
            $acc = $callback($acc, $val, $key);
        }

        return $acc;
    }
}

if (!function_exists('array_pull')) {
    function array_pull(array &$array, string $key, $default = null)
    {
        $value = $array[$key] ?? $default;
        unset ($array[$key]);

        return $value;
    }
}

if (!function_exists('str_camel')) {
    function str_camel(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        $value = str_replace(' ', '', $value);

        return lcfirst($value);
    }
}

if (!function_exists('array_object')) {
    /**
     * @param iterable $entries
     *
     * @return ArrayObject | \stdClass
     */
    function array_object(iterable $entries = [], int $options = ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST): ArrayObject
    {
        foreach ($entries as $key => $entry) {
            if (is_array($entry)) {
                $entries[$key] = array_object($entry, $options);
            }
        }

        return new ArrayObject($entries, $options);
    }
}
