<?php

declare(strict_types=1);

namespace PHP;

interface Jsonable extends \JsonSerializable, \Stringable
{
    public function toJson(int $options = 0): string;
}