<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver;

use phpDocumentor\Reflection\Type;
use RuntimeException;
use Throwable;

class InvalidTypeException extends RuntimeException
{
    public function __construct(
        public readonly Type $expectedType,
        public readonly mixed $value,
        int $code = 0, ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                "Provided value has invalid type: expected '%s' but got '%s'",
                $this->expectedType,
                gettype($this->value)
            ),
            $code,
            $previous
        );
    }
}