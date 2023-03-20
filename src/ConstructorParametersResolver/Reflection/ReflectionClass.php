<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Reflection;

use ReflectionClass as Base;
use ReflectionException;

class ReflectionClass extends Base
{
    public function getConstructor(): ?ReflectionMethod
    {
        try {
            return $this->getMethod('__construct');
        } catch (ReflectionException) {
            return null;
        }
    }

    public function getMethod(string $name): ReflectionMethod
    {
        return new ReflectionMethod("{$this->getName()}::$name");
    }
}