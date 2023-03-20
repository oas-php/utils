<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver\Event;

use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass;

abstract class ParamsResolution
{
    private ?array $params = null;

    public function __construct(public readonly ReflectionClass $reflection, public readonly array $originalParams)
    {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return is_null($this->params) ? $this->originalParams : $this->params;
    }
}
