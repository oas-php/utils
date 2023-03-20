<?php declare(strict_types=1);

namespace Tests\OAS\Utils\Helper;

class AddressUSA extends Address
{
    public readonly string $country;

    public function __construct(
        public readonly string $state,
        string $street,
        string $city,
        string $zipCode,
        ?PhoneNumber $phoneNumber = null
    )
    {
        parent::__construct(
            'USA',
            $street,
            $city,
            $zipCode,
            $phoneNumber
        );
    }
}