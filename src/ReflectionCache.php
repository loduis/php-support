<?php

declare(strict_types=1);

namespace PHP;

use ReflectionClass;
use ReflectionProperty;

final class ReflectionCache
{
    /** @var array<string, array{'class': ReflectionClass, 'properties': array<int, array<ReflectionProperty>>}> $cache */
    private static array $cache = [];

    /**
     * @param string $name
     * @return ReflectionClass
     * @throws \InvalidArgumentException
     */
    public static function getClass(string $name): ReflectionClass
    {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException("'$name' is not a valid class");
        }

        return static::$cache[$name]['class'] ?? (
            static::$cache[$name]['class'] = new ReflectionClass($name)
        );
    }
    /**
     * @param string $class
     * @param int $filter
     * @return array
     */
    public static function getProperties(string $class, int $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED): array
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return static::$cache[$class]['properties'][$filter] ?? (
            static::$cache[$class]['properties'][$filter] = static::getClass($class)->getProperties($filter)
        );
    }

    public static function getProperty(string $class, string $name): ReflectionProperty
    {
        return static::getClass($class)->getProperty($name);
    }
}