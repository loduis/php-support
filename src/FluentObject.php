<?php

declare(strict_types=1);

namespace Php;

use ReflectionNamedType;
use OutOfBoundsException;
use ReflectionProperty;
use ArrayAccess;
use stdClass;
use Throwable;

abstract class FluentObject implements ArrayAccess, Arrayable
{
    public function __construct(array $properties = [])
    {
        $this->syncProperties($properties);
    }

    public function syncProperties(iterable $properties): static
    {
        foreach ($properties as $property => $value) {
            $this->$property = $this->setPropertyValue(
                ReflectionCache::getProperty(static::class, $property),
                $value,
                true
            );
        }

        return $this;
    }

    public function offsetSet($offset, $value): void
    {
        if (!property_exists($this, $offset)) {
            throw new OutOfBoundsException("Property does not exist: $offset on class " . static::class);
        }
        $property = ReflectionCache::getProperty(static::class, $offset);
        if ($property->isPrivate()) {
            throw new OutOfBoundsException("Cannot access private property: $offset");
        }
        $this->$offset = $this->setPropertyValue($property, $value);
    }

    public function offsetExists($offset): bool
    {
        if (!property_exists($this, $offset)) {
            return false;
        }
        try {
            $this->$offset !== null; // cuando esta unset lanza error
        } catch (Throwable) {
            return false;
        }
        return !ReflectionCache::getProperty(static::class, $offset)
            ->isPrivate();
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->$offset);
        }
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Property does not exist or is private: $offset");
        }

        return $this->getPropertyValue($offset, $this->$offset);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    public function toArray(): iterable
    {
        $array = [];
        foreach (ReflectionCache::getClass(static::class)->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
            $key = $property->name;
            try {
                $value = $this->$key;
            } catch (Throwable $err) {
                $type = $property->getType();
                if (!$type || !($type instanceof ReflectionNamedType) || !$type->allowsNull()) {
                    throw $err;
                }
                continue;
            }
            $value = $this->getPropertyValue($key, $value);
            $array[$key] = $value instanceof Arrayable ? $value->toArray() : $value;
        }

        return $array;
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }

    private function setPropertyValue(ReflectionProperty $property, $value, bool $force = false)
    {
        $key = $property->getName();
        $setterMethod = 'set' . ucfirst($key);
        if (method_exists($this, $setterMethod)) {
            $value = $this->$setterMethod($value);
        }
        if (is_iterable($value) || ($value instanceof stdClass)) {
            $type = $property->getType();
            if ($type instanceof ReflectionNamedType) {
                $className = ReflectionCache::getClass($type->getName());
                if ($className->isSubclassOf(self::class)) {
                    $value = $className->newInstance((array) $value);
                }
            }
        }

        if (!$force && version_compare(PHP_VERSION, '8.1.0', '<')) {
            $comments = $property->getDocComment();
            if ($comments && str_contains($comments, '@readonly')) {
                throw new \LogicException("Property $key is readonly on class " . static::class);
            }
        }

        return $value;
    }

    private function getPropertyValue(string $name, $value)
    {
        $getterMethod = 'get' . ucfirst($name);
        if (method_exists($this, $getterMethod)) {
            return $this->$getterMethod($value);
        }

        return $value;
    }
}