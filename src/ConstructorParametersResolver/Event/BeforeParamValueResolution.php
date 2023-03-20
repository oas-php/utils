<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Event;

use phpDocumentor\Reflection\Type;

class BeforeParamValueResolution extends BeforeValueResolution
{
    public function __construct(public readonly string $name, Type $type, mixed $rawValue)
    {
        parent::__construct($type, $rawValue);
    }
}
