<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Event;

use phpDocumentor\Reflection\Type;

class AfterParamValueResolution extends AfterValueResolution
{
    public function __construct(public readonly string $name, Type $type, mixed $value)
    {
        parent::__construct($type, $value);
    }
}
