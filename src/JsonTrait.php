<?php

declare(strict_types=1);

namespace Php;

trait JsonTrait
{
    /**
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_BIGINT_AS_STRING |
            JSON_PRESERVE_ZERO_FRACTION |
            JSON_THROW_ON_ERROR |
            $options
        );
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}