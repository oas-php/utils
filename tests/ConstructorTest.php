<?php declare(strict_types=1);

namespace Tests\OAS\Utils;

use OAS\Utils\Constructor;
use OAS\Utils\ConstructorParametersResolver;
use PHPUnit\Framework\TestCase;
use Tests\OAS\Utils\Helper\AddressCanada;
use Tests\OAS\Utils\Helper\AddressUSA;
use Tests\OAS\Utils\Helper\Employee;

class ConstructorTest extends TestCase
{
    /**
     * @test
     */
    public function itConstructsObject(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $number = 888414871;
        $countryCode = 48;
        $addressCanada = [
            'province' => 'ON',
            'street' => '153 Bank St',
            'city' => 'Ottawa',
            'zipCode' => 'K1P 5N7',
            'phoneNumber' => [
                'countryCode' => 2,
                'number' => 6135696505
            ]
        ];
        $addressUSA = [
            'state' => 'NY',
            'street' => '315 W 44th St #5402',
            'city' => 'New York',
            'zipCode' => '10036',
            'phoneNumber' => [
                'number' => 2125813080
            ]
        ];

        $object = (new Constructor(new ConstructorParametersResolver))->construct(
            Employee::class,
            [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'addresses' => [
                    $addressCanada,
                    $addressUSA
                ],
                'phoneNumber' => [
                    'countryCode' => $countryCode,
                    'number' => $number
                ]
            ]
        );

        self::assertInstanceOf(Employee::class, $object);
        self::assertEquals($firstName, $object->firstName);
        self::assertEquals($lastName, $object->lastName);
        self::assertInstanceOf(AddressCanada::class, $object->addresses[0]);
        self::assertEquals($addressCanada['province'], $object->addresses[0]->province);
        self::assertEquals($addressCanada['street'], $object->addresses[0]->street);
        self::assertEquals($addressCanada['city'], $object->addresses[0]->city);
        self::assertEquals($addressCanada['zipCode'], $object->addresses[0]->zipCode);
        self::assertEquals($addressCanada['phoneNumber']['number'], $object->addresses[0]->phoneNumber->number);
        self::assertInstanceOf(AddressUSA::class, $object->addresses[1]);
        self::assertEquals($addressUSA['state'], $object->addresses[1]->state);
        self::assertEquals($addressUSA['street'], $object->addresses[1]->street);
        self::assertEquals($addressUSA['city'], $object->addresses[1]->city);
        self::assertEquals($addressUSA['zipCode'], $object->addresses[1]->zipCode);
        self::assertEquals($addressUSA['phoneNumber']['number'], $object->addresses[1]->phoneNumber->number);
        self::assertEquals($countryCode, $object->phoneNumber->countryCode);
        self::assertEquals($number, $object->phoneNumber->number);
    }
}