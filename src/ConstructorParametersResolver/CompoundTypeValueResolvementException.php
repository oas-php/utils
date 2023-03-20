<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver;

use Exception;
use Throwable;

class CompoundTypeValueResolvementException extends Exception
{
    /**
     * @param array<Throwable> $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct();
    }
}
