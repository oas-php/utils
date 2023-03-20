<?php declare(strict_types=1);

namespace Tests\OAS\Utils;

use Generator;
use OAS\Utils\CodeGenerator;
use OAS\Utils\ConstructorParametersResolver;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter;
use PHPUnit\Framework\TestCase;
use Tests\OAS\Utils\Helper\Employee;

class CodeGeneratorTest extends TestCase
{
    /**
     * @test
     * @dataProvider rawParametersDataProvider
     */
    public function itGeneratesCode(array $rawParameters): void
    {
        self::assertEquals(
            <<<CODE
            new Tests\OAS\Utils\Helper\Employee('John', 'Doe', array(new Tests\OAS\Utils\Helper\AddressUSA('NY', '315 W 44th St #5402', 'New York', '10036', new Tests\OAS\Utils\Helper\PhoneNumber(2125813080, 1))), new Tests\OAS\Utils\Helper\PhoneNumber(888414871, 48), null)
            CODE,
            (new CodeGenerator(new ConstructorParametersResolver))->generate(Employee::class, $rawParameters)
        );
    }

    /**
     * @test
     * @dataProvider rawParametersDataProvider
     */
    public function itGeneratesAST(array $rawParameters): void
    {
        $object = null;

        // generated code is assigned to $object variable
        $code = (new PrettyPrinter\Standard)->prettyPrint(
            [
                new Expression(
                    new Assign(
                        new Variable('object'),
                            (new CodeGenerator(new ConstructorParametersResolver))->generateAST(
                                Employee::class,
                                $rawParameters
                            )
                    )
                )
            ]
        );

        eval($code);

        $this->assertObjectValid($object);
    }

    public function rawParametersDataProvider(): Generator
    {
        yield [
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'addresses' => [
                    [
                        'state' => 'NY',
                        'street' => '315 W 44th St #5402',
                        'city' => 'New York',
                        'zipCode' => '10036',
                        'phoneNumber' => [
                            'number' => 2125813080
                        ]
                    ]
                ],
                'phoneNumber' => [
                    'countryCode' => 48,
                    'number' => 888414871
                ]
            ]
        ];
    }

    private function assertObjectValid(mixed $object): void
    {
        self::assertInstanceOf(Employee::class, $object);
        self::assertEquals('John', $object->firstName);
        self::assertEquals('Doe', $object->lastName);
        self::assertEquals('NY', $object->addresses[0]->state);
        self::assertEquals('315 W 44th St #5402', $object->addresses[0]->street);
        self::assertEquals('New York', $object->addresses[0]->city);
        self::assertEquals(1, $object->addresses[0]->phoneNumber->countryCode);
        self::assertEquals(2125813080, $object->addresses[0]->phoneNumber->number);
        self::assertEquals(48, $object->phoneNumber->countryCode);
        self::assertEquals(888414871, $object->phoneNumber->number);
    }
}