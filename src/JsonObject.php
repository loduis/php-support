<?php

declare(strict_types=1);

namespace Php;

abstract class JsonObject extends FluentObject implements Jsonable
{
    use JsonTrait;
}