<?php

declare(strict_types=1);

namespace PHP;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): ?iterable;
}
