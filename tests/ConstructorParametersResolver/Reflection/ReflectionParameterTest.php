<?php declare(strict_types=1);

namespace Tests\OAS\Utils\ConstructorParametersResolver\Reflection;

use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass;
use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionParameter;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Array_;
use PHPUnit\Framework\TestCase;
use Tests\OAS\Utils\Helper\Employee;

class ReflectionParameterTest extends TestCase
{
    /**
     * @test
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass::getConstructor
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass::getMethod
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionMethod::__construct
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionMethod::getParameters
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionParameter::__construct
     * @covers \OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionParameter::getExtendedType
     */
    public function itReturnsValidExtendedType(): void
    {
        $parameters = (new ReflectionClass(Employee::class))
            ->getConstructor()
            ->getParameters();

        $this->assertContainsOnlyInstancesOf(ReflectionParameter::class, $parameters);

        $parameter0 = $parameters[0];
        $this->assertInstanceOf(String_::class, $parameter0->getExtendedType());

        $parameter1 = $parameters[1];
        $this->assertInstanceOf(String_::class, $parameter1->getExtendedType());

        $parameter2 = $parameters[2];
        $parameter2ExtendedType = $parameter2->getExtendedType();
        $this->assertInstanceOf(Nullable::class, $parameter2ExtendedType);

        $parameter2ActualType = $parameter2ExtendedType->getActualType();
        $this->assertInstanceOf(Array_::class, $parameter2ActualType);

        $parameter2ValueType = $parameter2ActualType->getValueType();
        $this->assertInstanceOf(Compound::class, $parameter2ValueType);

        $this->assertEquals(
            '\Tests\OAS\Utils\Helper\AddressCanada|\Tests\OAS\Utils\Helper\AddressUSA',
            (string) $parameter2ValueType
        );

        $parameter2 = $parameters[3];
        $this->assertInstanceOf(Object_::class, $parameter2->getExtendedType());
    }
}