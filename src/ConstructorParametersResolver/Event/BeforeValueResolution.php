<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Event;

use phpDocumentor\Reflection\Type;

class BeforeValueResolution
{
    private mixed $value = null;
    private bool $valueResolved = false;
    private bool $rawValueUpdated = false;

    public function __construct(public readonly Type $type, private mixed $rawValue)
    {
    }

    public function setValue(mixed $value): void
    {
        $this->valueResolved = true;
        $this->value = $value;
    }

    public function isValueResolved(): bool
    {
        return $this->valueResolved;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setRawValue(mixed $rawValue): void
    {
        $this->rawValueUpdated = true;
        $this->rawValue = $rawValue;
    }

    public function isRawValueUpdated(): bool
    {
        return $this->rawValueUpdated;
    }

    public function getRawValue(): mixed
    {
        return $this->rawValue;
    }
}
