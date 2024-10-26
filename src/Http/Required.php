<?php

namespace Php\Http;

#[\Attribute]
class Required
{
    private array $methods;

    public function __construct(string ...$methods)
    {
        $this->methods = array_map(
            function (string $method) {
                $method = strtoupper($method);
                if (!in_array($method, ['POST', 'PUT', 'PATH', 'DELETE'])) {
                    throw new \InvalidArgumentException(
                        "Invalid HTTP method: '$method'."
                    );
                }
                return $method;
            }, $methods
        );
    }

    public function validate(string $method, string $key, mixed $value): bool
    {
        if (in_array($method, $this->methods) && $value === null) {
            throw new \InvalidArgumentException("'$key' property is required for $method requests");
        }

        return true;
    }
}