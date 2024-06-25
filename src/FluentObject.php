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
                ReflectionCache::getProperty(static::class, $property), $value
            );
        }

        return $this;
    }

    public function offsetSet($offset, $value): void
    {
        if (!property_exists($this, $offset)) {
            throw new OutOfBoundsException("Property does not exist: $offset");
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
        foreach ($this as $key => $value) {
            $property = ReflectionCache::getProperty(static::class, $key);
            if (!$property->isPrivate()) {
                $value = $this->getPropertyValue($key, $value);
                $array[$key] = $value instanceof Arrayable ? $value->toArray() : $value;
            }
        }

        return $array;
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }

    private function setPropertyValue(ReflectionProperty $property, $value)
    {
        $setterMethod = 'set' . ucfirst($property->getName());
        if (method_exists($this, $setterMethod)) {
            $value = $this->$setterMethod($value);
        } elseif (is_iterable($value) || ($value instanceof stdClass)) {
            $type = $property->getType();
            if ($type instanceof ReflectionNamedType) {
                $className = ReflectionCache::getClass($type->getName());
                if ($className->isSubclassOf(self::class)) {
                    $value = $className->newInstance((array) $value);
                }
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