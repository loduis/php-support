<?php

declare(strict_types=1);

namespace Php;

class ArrayObject extends \ArrayObject implements \JsonSerializable
{
    public function toArray(): array
    {
        $array = [];
        foreach ($this->getArrayCopy() as $key => $value) {
            $array[$key] = $value instanceof static ? $value->toArray() : $value;
        }

        return $array;
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    public function offsetGet($index)
    {
        return $this->offsetExists($index) ? parent::offsetGet($index) : null;
    }

    public function offsetUnset($index): void
    {
        if ($this->offsetExists($index)) {
            parent::offsetUnset($index);
        }
    }

    public function __toString()
    {
        return $this->toJSON();
    }

    public function pretty()
    {
        return $this->toJSON(JSON_PRETTY_PRINT);
    }

    public function toJSON($options = 0)
    {
        return json_encode($this,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_BIGINT_AS_STRING |
            JSON_PRESERVE_ZERO_FRACTION |
            $options
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function pull(string $key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }
        $value = $this->offsetGet($key);
        $this->offsetUnset($key);

        return $value;
    }

    public function all(array $params)
    {
        $count = count($params);
        $res = [];
        foreach ($this as $entry) {
            $exact = 0;;
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

    public function find(array $params)
    {
        $count = count($params);
        foreach ($this as $entry) {
            $exact = 0;;
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
