<?php

declare(strict_types=1);

namespace PHP\Attribute;

#[\Attribute]
class Transform
{
    public const CAMEL = 'camel';

    public const DASH = 'dash';

    public const SNAKE = 'snake';

    public const LOWER = 'lower';

    public const UPPER = 'upper';

    public const CUSTOM = 'custom';

    public function __construct(private string $type, private ?string $name = null)
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}