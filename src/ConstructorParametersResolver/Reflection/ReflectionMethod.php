<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Reflection;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionException;
use ReflectionMethod as Base;

class ReflectionMethod extends Base
{
    private DocBlockFactory $dockBlockFactory;

    public function __construct(object|string $objectOrMethod, ?string $method = null)
    {
        parent::__construct($objectOrMethod, $method);
        $this->dockBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * @throws ReflectionException
     * @return array<ReflectionParameter>
     */
    public function getParameters(): array
    {
        $numberOfParameters = $this->getNumberOfParameters();
        $parameters = [];

        if ($numberOfParameters > 0) {
            $class = $this->getDeclaringClass()->getName();
            $method = $this->getName();
            $docBlockComment = $this->getDocComment();

            $paramTags = is_string($docBlockComment)
                ? array_reduce(
                    array_filter(
                        $this->dockBlockFactory->create($this->getDocComment())->getTags(),
                        fn (Tag $tag) => $tag instanceof Param
                    ),
                    function (array $paramTagMap, Param $param) {
                        $paramTagMap[$param->getVariableName()] = $param;

                        return $paramTagMap;
                    },
                    []
                )
                : [];

            for ($index = 0; $index < $numberOfParameters; $index++) {
                $parameters[] = new ReflectionParameter([$class, $method], $index, $paramTags);
            }
        }

        return $parameters;
    }
}