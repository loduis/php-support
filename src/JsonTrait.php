<?php

declare(strict_types=1);

namespace Php;

trait JsonTrait
{
    public function toJson($options = 0)
    {
        return json_encode($this,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_BIGINT_AS_STRING |
            JSON_PRESERVE_ZERO_FRACTION |
            $options
        );
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}