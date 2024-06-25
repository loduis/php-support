<?php

declare(strict_types=1);

namespace Php;

use ReflectionClass;
use ReflectionProperty;

final class ReflectionCache
{
    private static array $cache = [];

    public static function getClass(string $name): ReflectionClass
    {
        return static::$cache[$name] ?? (
            static::$cache[$name] = new ReflectionClass($name)
        );
    }

    public static function getProperty(string $class, string $name): ReflectionProperty
    {
        return static::getClass($class)->getProperty($name);
    }
}