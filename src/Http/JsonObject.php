<?php

declare(strict_types=1);

namespace PHP\Http;

use PHP\JsonObject as PhpJsonObject;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends PhpJsonObject<TKey,TValue>
 */
abstract class JsonObject extends PhpJsonObject
{
    use JsonTrait;
}