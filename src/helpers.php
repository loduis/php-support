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

if (!function_exists('array_key_exists')) {

    function array_key_exists(iterable $array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return \array_key_exists($key, (array) $array);
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

if (!function_exists('is_array')) {
    function is_array($value) {
        return \is_array($value) || $value instanceof \ArrayAccess;
    }
}

if (!function_exists('array_has')) {

    function array_has($array, ...$keys)
    {
        if (! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (array_key_exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (is_array($subKeyArray) && array_key_exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('str_camel')) {

    function str_camel(string $value): string
    {
        static $cache = [];

        if (isset($cache[$value])) {
            return $cache[$value];
        }

        return $cache[$value] = lcfirst(
            str_replace(
                ' ',
                '',
                ucwords(
                    str_replace(['-', '_'], ' ', $value)
                )
            )
        );
    }
}

if (!function_exists('str_snake')) {

    function str_snake(string $value, string $delimiter = '_')
    {
        static $cache = [];
        $key = $value;

        if (isset($cache[$key][$delimiter])) {
            return $cache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = mb_strtolower(
                preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value),
                'UTF-8'
            );
        }

        return $cache[$key][$delimiter] = $value;
    }
}

if (!function_exists('number_pad')) {
    function number_pad($value, int $num = 2): string
    {
        return str_pad(
            (string) $value,
            $num,
            '0',
            STR_PAD_LEFT
        );
    }
}