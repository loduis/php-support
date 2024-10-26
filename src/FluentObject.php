<?php

declare(strict_types=1);

namespace Php;

use ReflectionNamedType;
use OutOfBoundsException;
use ReflectionProperty;
use ArrayAccess;
use stdClass;
use Throwable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey,TValue>
*/
abstract class FluentObject implements ArrayAccess, Arrayable
{
    public function __construct(iterable $properties = [])
    {
        $this->syncProperties($properties);
    }

    public function syncProperties(iterable $properties, array $transforms = []): static
    {
        /**
         * @var iterable<string|int, mixed> $properties
         * @var mixed $value
         */
        foreach ($properties as $property => $value) {
            /** @var string */
            $key = $transforms[$property] ?? $property;
            if (is_string($property)) {
                /** @var mixed */
                $value = $this->setPropertyValue(
                    ReflectionCache::getProperty(static::class, $key),
                    $value,
                    true
                );
            }
            $this->$key = $value;
        }

        return $this;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset !== null && is_string($offset)) {
            if (!property_exists($this, $offset)) {
                throw new OutOfBoundsException("Property does not exist: $offset on class " . static::class);
            }

            $property = ReflectionCache::getProperty(static::class, $offset);
            if ($property->isPrivate()) {
                throw new OutOfBoundsException("Cannot access private property: $offset");
            }
            $this->$offset = $this->setPropertyValue($property, $value);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        if (is_string($offset) && !property_exists($this, $offset)) {
            return false;
        }
        try {
            $this->$offset !== null; // cuando esta unset lanza error
        } catch (Throwable) {
            return false;
        }

        if (!is_string($offset)) {
            return false;
        }

        return !ReflectionCache::getProperty(static::class, $offset)
            ->isPrivate();
    }

    /**
     * @param TKey $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->$offset);
        }
    }

    /**
     * @param TKey $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Property does not exist or is private: $offset");
        }

        return $this->getPropertyValue($offset, $this->$offset);
    }

    /**
     * @param TKey $key
     * @return mixed
     */
    public function __get(mixed $key): mixed
    {
        return $this->offsetGet($key);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     * @return void
     */
    public function __set(mixed $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }
    /**
     * @param TKey $key
     * @return bool
     */
    public function __isset(mixed $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param TKey $key
     * @return void
     */
    public function __unset(mixed $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * @return (array|mixed|null)[]
     *
     * @psalm-return array<string, array|mixed|null>
     */
    public function toArray(): array
    {
        $array = [];
        /** @var ReflectionProperty $property */
        foreach (ReflectionCache::getProperties(static::class) as $property) {
            /** @var string */
            $key = $property->name;
            $value = null;
            try {
                /** @var mixed */
                $value = $this->$key;
            } catch (Throwable $err) {
                $type = $property->getType();
                if (!$type || !($type instanceof ReflectionNamedType) || !$type->allowsNull()) {
                    throw $err;
                }
                continue;
            }
            /** @var mixed */
            $value = $this->getPropertyValue($key, $value, $property);
            $key = $this->getKey($key, $property);
            /** @var mixed */
            $array[$key] = $value instanceof Arrayable ? $value->toArray() : $value;
        }

        return $array;
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    protected function getKey(string $key, ?ReflectionProperty $property = null): string
    {
        return $key;
    }

    /**
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param boolean $force
     * @return mixed
     */
    private function setPropertyValue(ReflectionProperty $property, mixed $value, bool $force = false): mixed
    {
        $key = $property->getName();
        $setterMethod = 'set' . ucfirst($key);
        if (method_exists($this, $setterMethod)) {
            /** @psalm-suppress MixedAssignment */
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
            if ((string) ($comments) !== '' && str_contains($comments, '@readonly')) {
                throw new \LogicException("Property $key is readonly on class " . static::class);
            }
        }

        return $value;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return mixed
     */
    protected function getPropertyValue(mixed $name, mixed $value, ?ReflectionProperty $property = null): mixed
    {
        if (is_string($name)) {
            $getterMethod = 'get' . ucfirst($name);
            if (method_exists($this, $getterMethod)) {
                return $this->$getterMethod($value);
            }
        }

        return $value;
    }
}