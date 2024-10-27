<?php

declare(strict_types=1);

namespace PHP\Attribute;

use ReflectionProperty;
use PHP\ReflectionCache;
use function PHP\str_camel;
use function PHP\str_snake;

trait TransformTrait
{

    public function syncProperties(iterable $properties, array $transforms = []): static
    {
        parent::syncProperties(
            $properties,
            $this->getPropertyTransforms()
        );

        return $this;
    }

    protected function getKey(string $key, ?ReflectionProperty $property = null): string
    {
        $key = parent::getKey($key, $property);
        if ($property !== null) {
            $transformAttribute = $property->getAttributes(Transform::class)[0] ?? null;
            if ($transformAttribute !== null) {
                $transform = $transformAttribute->newInstance();
                $key = $this->getTransformedKey($key, $transform);
            }
        }

        return $key;
    }

    private function getTransformedKey(string $key, Transform $transform): string
    {
        return match ($transform->getType()) {
            Transform::CAMEL => str_camel($key),
            Transform::DASH => str_snake($key, '-'),
            Transform::SNAKE => str_snake($key),
            Transform::LOWER => mb_strtolower($key),
            Transform::UPPER => mb_strtoupper($key),
            default => (string) $transform->getName()
        };
    }

    protected function getPropertyTransforms(): array
    {
        /** @var array<mixed,mixed> */
        $transforms = [];
        /** @var ReflectionProperty $property */
        foreach (ReflectionCache::getProperties(static::class) as $property) {
            $transformAttribute = $property->getAttributes(Transform::class)[0] ?? null;
            /** @var ?\ReflectionAttribute */
            if ($transformAttribute) {
                /** @var Transform */
                $transform = $transformAttribute->newInstance();
                $transforms[$this->getTransformedKey($property->name, $transform)] = $property->name;
            } else {
                $transforms[$property->name] = $property->name;
            }
        }

        return $transforms;
    }
}
