<?php

declare(strict_types=1);

namespace Php;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends FluentObject<TKey,TValue>
 */
abstract class JsonObject extends FluentObject implements Jsonable
{
    use JsonTrait;
}