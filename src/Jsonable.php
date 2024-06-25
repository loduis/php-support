<?php

declare(strict_types=1);

namespace Php;

interface Jsonable extends \JsonSerializable, \Stringable
{
    public function toJson(int $options = 0);
}