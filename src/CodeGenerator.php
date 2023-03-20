<?php declare(strict_types=1);

namespace OAS\Utils;

use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\New_;
use ReflectionException;
use PhpParser\PrettyPrinter;

class CodeGenerator
{
    public function __construct(private ConstructorParametersResolver $constructorParametersResolver)
    {
    }

    public function generate(string $class, array $parameters): string
    {
        return (new PrettyPrinter\Standard)->prettyPrintExpr($this->generateAST($class, $parameters));
    }

    /**
     * @param array<string, mixed> $params
     * @throws ReflectionException
     */
    public function generateAST(string $type, array $params = []): New_
    {
        $factory = new BuilderFactory;

        return $this->constructorParametersResolver->resolve(
            $type,
            $params,
            /** @param array<string, mixed> $params */
            fn (ReflectionClass $reflection, array $params): New_ => $factory->new($reflection->getName(), $params)
        );
    }
}