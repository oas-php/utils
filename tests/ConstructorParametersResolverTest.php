<?php declare(strict_types=1);

namespace Tests\OAS\Utils;

use Generator;
use OAS\Utils\ConstructorParametersResolver;
use OAS\Utils\ConstructorParametersResolver\ValueResolvementException;
use PHPUnit\Framework\TestCase;
use Tests\OAS\Utils\Helper\Employee;

class ConstructorParametersResolverTest extends TestCase
{
    /**
     * @test
     * @dataProvider invalidParametersDataProvider
     */
    public function itFailsToResolveParametersWhenInvalidRawParametersProvided(
        string $class,
        array $rawParameters,
        array $expectedException
    ): void
    {
        try {
            (new ConstructorParametersResolver)->resolve($class, $rawParameters, fn () => null);
            $this->fail(sprintf('The %s exception should be thrown', ValueResolvementException::class));
        } catch (ValueResolvementException $exception) {
            self::assertExceptionThrownCorrectly($exception, $expectedException);
        }
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function invalidParametersDataProvider(): Generator
    {
        yield [
            'class' => Employee::class,
            'rawParameters' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'addresses' => [
                    [
                        'state' => 'NY',
                        'street' => '315 W 44th St #5402',
                        'city' => 'New York',
                        'zipCode' => '10036',
                        'phoneNumber' => [
                            // invalid type!
                            'number' => '+48 888 414 491'
                        ]
                    ]
                ],
                'phoneNumber' => [
                    'countryCode' => 48,
                    'number' => 888414871
                ]
            ],
            'expectedException' => [
                'path' => ['addresses', '0'],
                'errors' => [
                    [
                        'path' => ['province'],
                        'errors' => [
                            "Provided value has invalid type: expected 'string' but got 'NULL'"
                        ]
                    ],
                    [
                        'path' => ['phoneNumber', 'number'],
                        'errors' => [
                            "Provided value has invalid type: expected 'int' but got 'string'",
                        ]
                    ]
                ]
            ]
        ];

        yield [
            'cass' => Employee::class,
            'rawParameters' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'addresses' => [
                    [
                        'state' => 'NY',
                        'street' => '315 W 44th St #5402',
                        'country' => 'USA',
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
                ],
                'subordinates' => [
                    [
                        'firstName' => 'Simon',
                        'lastName' => 'Smith',
                        'phoneNumber' => [
                            'countryCode' => 48,
                            'number' => 888414871
                        ]
                    ],
                    [
                        'firstName' => 'Carl',
                        'lastName' => 'Griffin',
                        'phoneNumber' => [
                            'countryCode' => 48,
                            'number' => 888414871
                        ],
                        'subordinates' => [
                            [
                                'firstName' => 'Mike',
                                'lastName' => 'Vogt',
                                'phoneNumber' => [
                                    // invalid type!
                                    'countryCode' => '+48',
                                    'number' => 888414871
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'expectedException' => [
                'path' => ['subordinates', '1', 'subordinates', '0', 'phoneNumber', 'countryCode'],
                'errors' => [
                    'message' => "Provided value has invalid type: expected 'int' but got 'string'"
                ]
            ]
        ];
    }

    private function assertExceptionThrownCorrectly(ValueResolvementException $actual, array $expected): void
    {
        self::assertEquals($expected['path'], $actual->getPath());

        $errors = $actual->getErrors();
        self::assertCount(count($expected['errors']), $errors);

        foreach ($expected['errors'] as $expectedError) {
            $error = array_shift($errors);

            if (is_string($expectedError)) {
                self::assertEquals($expectedError, $error->getMessage());
            } else {
                assert(is_array($expectedError));
                self::assertInstanceOf(ValueResolvementException::class, $error);
                $this->assertExceptionThrownCorrectly($error, $expectedError);
            }
        }
    }
}