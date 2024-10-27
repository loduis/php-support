<?php

declare(strict_types=1);

namespace PHP;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends \ArrayObject<TKey,TValue>
 */
class ArrayObject extends \ArrayObject implements Arrayable, Jsonable
{
    use JsonTrait;

    public function toArray(): array
    {
        $array = [];
        foreach ($this->getArrayCopy() as $key => $value) {
            /** @disregard P1013 */
            $array[$key] = $value instanceof static ? $value->toArray() : $value;
        }

        return $array;
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    public function offsetGet(mixed $index): mixed
    {
        return $this->offsetExists($index) ? parent::offsetGet($index) : null;
    }

    public function offsetUnset(mixed $index): void
    {
        if ($this->offsetExists($index)) {
            parent::offsetUnset($index);
        }
    }

    /**
     *
     * @param TKey $key
     * @return mixed
     */
    public function pull(string $key): mixed
    {
        if (!$this->offsetExists($key)) {
            return null;
        }
        $value = $this->offsetGet($key);
        $this->offsetUnset($key);

        return $value;
    }

    public function all(array $params): array
    {
        $count = count($params);
        $res = [];
        foreach ($this as $entry) {
            $exact = 0;;
            /** @var TKey $key
              * @var TValue $value
              */
            foreach ($params as $key => $value) {
                if ($value != $entry->$key) {
                    break;
                }
                $exact ++;
            }
            if ($count === $exact) {
                $res[] = $entry;
            }
        }

        return $res;
    }

    public function find(array $params): mixed
    {
        $count = count($params);
        foreach ($this as $entry) {
            $exact = 0;;
            /** @var TKey $key
              * @var TValue $value
              */
            foreach ($params as $key => $value) {
                if ($value != $entry->$key) {
                    break;
                }
                $exact ++;
            }
            if ($count === $exact) {
                return $entry;
            }
        }

        return null;
    }
}
