<?php declare(strict_types=1);

namespace OAS\Utils;

use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass;
use ReflectionException;

class Constructor
{
    public function __construct(private ConstructorParametersResolver $constructorParametersResolver)
    {
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @param array<string, mixed> $params
     * @return T
     * @throws ReflectionException
     */
    public function construct(string $type, array $params): object
    {
        return $this->constructorParametersResolver->resolve(
            $type,
            $params,
            fn (ReflectionClass $reflection, $params) => $reflection->newInstance(...$params)
        );
    }
}