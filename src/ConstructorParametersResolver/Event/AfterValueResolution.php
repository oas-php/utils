<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Event;

use phpDocumentor\Reflection\Type;

class AfterValueResolution
{
    public function __construct(public readonly Type $type, private mixed $value)
    {
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
