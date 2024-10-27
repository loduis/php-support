<?php

namespace PHP {
    if (!function_exists(__NAMESPACE__ . '\array_reduce')) {
        function array_reduce(array $array, callable $callback, mixed $initial = null): mixed
        {
            /** @var mixed */
            $acc = $initial;
            /** @var mixed $val */
            foreach ($array as $key => $val) {
                /** @var mixed */
                $acc = $callback($acc, $val, $key);
            }

            return $acc;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\array_pull')) {
        function array_pull(array &$array, string $key, mixed $default = null): mixed
        {
            /** @var mixed */
            $value = $array[$key] ?? $default;
            unset ($array[$key]);

            return $value;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\array_key_exists')) {
        function array_key_exists(iterable $array, string|int $key): bool
        {
            if ($array instanceof \ArrayAccess) {
                return $array->offsetExists($key);
            }

            return \array_key_exists($key, (array) $array);
        }
    }

    if (!function_exists(__NAMESPACE__ . '\array_object')) {
        /**
         * @param iterable $entries
         *
         * @return ArrayObject
         */
        function array_object(iterable $entries = [], int $options = ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST): ArrayObject
        {
            /**
             * @var array<array-key, mixed> $entries
             * @var mixed $entry
             */
            foreach ($entries as $key => $entry) {
                if (is_array($entry)) {
                    /** @var iterable<array-key, mixed> $entry */
                    $entries[$key] = array_object($entry, $options);
                }
            }

            return new ArrayObject($entries, $options);
        }
    }

    if (!function_exists(__NAMESPACE__ . '\is_array')) {
        function is_array(mixed $value): bool {
            return \is_array($value) || $value instanceof \ArrayAccess;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\array_has')) {

        function array_has(mixed $array, int|string ...$keys): mixed
        {
            if (! $array || $keys === []) {
                return false;
            }
            /**  @var int|string */
            foreach ($keys as $key) {
                /**  @var array */
                $subKeyArray = $array;
                /** @var array<mixed, mixed> $array */
                if (array_key_exists($array, $key)) {
                    continue;
                }

                /** @var string $key */
                foreach (explode('.', $key) as $segment) {
                    if (is_array($subKeyArray) && array_key_exists($subKeyArray, $segment)) {
                        /** @var array */
                        $subKeyArray = $subKeyArray[$segment];
                    } else {
                        return false;
                    }
                }
            }

            return true;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\str_camel')) {

        function str_camel(string $value): string
        {
            /** @var array<string, string> $cache */
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

    if (!function_exists(__NAMESPACE__ . '\str_snake')) {

        function str_snake(string $value, string $delimiter = '_'): string
        {
            /** @var array<string, array<string, mixed>> $cache */
            static $cache = [];
            $key = $value;

            if (isset($cache[$key][$delimiter])) {
                /** @var string */
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

    if (!function_exists(__NAMESPACE__ . '\number_pad')) {
        function number_pad(int|string $value, int $num = 2): string
        {
            return str_pad(
                (string) $value,
                $num,
                '0',
                STR_PAD_LEFT
            );
        }
    }
}

namespace {
    if (!function_exists('array_is_list')) {
        function array_is_list(array $array): bool
        {
            $keys = array_keys($array);

            return array_keys($keys) === $keys;
        }
    }
}