<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Reflection;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use ReflectionException;
use ReflectionParameter as Base;

class ReflectionParameter extends Base
{
    private Type $extendedType;

    /**
     * @param ?array<string, Param> $paramTags
     * @throws ReflectionException
     */
    public function __construct($function, int|string $param, ?array $paramTags = null)
    {
        parent::__construct($function, $param);
        $this->extendedType = array_key_exists($this->name, $paramTags)
            ? $paramTags[$this->name]->getType()
            : (new TypeResolver())->resolve((string) $this->getType());
    }

    /**
     * @throws ReflectionException
     */
    public function getDeclaringClass(): ReflectionClass
    {
        return new ReflectionClass(parent::getDeclaringClass()->getName());
    }

    public function getExtendedType(): Type
    {
        return $this->extendedType;
    }
}