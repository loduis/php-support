<?php

declare(strict_types=1);

namespace PHP\Http;

use PHP\Attribute\TransformTrait;
use ReflectionProperty;

trait JsonTrait
{
    use TransformTrait;

    private string $_httpMethod;

    protected function getPropertyValue(mixed $name, mixed $value, ?ReflectionProperty $property = null): mixed
    {
        /**
         * @var mixed
         */
        $value = parent::getPropertyValue($name, $value);
        if ($property !== null) {
            $requiredAttribute = $property->getAttributes(Required::class)[0] ?? null;
            /** @disregard P1013 */
            if ($requiredAttribute !== null &&
                $requiredAttribute
                    ->newInstance()
                    ->validate($this->_httpMethod, (string)  $name, $value)
            ) {
                    throw new \InvalidArgumentException(
                        "'$name' property is required for $this->_httpMethod requests"
                    );
            }
        }

        return $value;
    }

    public function httpMethod(string $method): void
    {
        $method = strtoupper($method);

        if (!in_array($method, ['POST', 'PUT', 'PATH', 'DELETE'])) {
            throw new \InvalidArgumentException(
                "Invalid HTTP method: '$method'."
            );
        }

        $this->_httpMethod = $method;
    }
}