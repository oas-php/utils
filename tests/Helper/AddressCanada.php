<?php declare(strict_types=1);

namespace Tests\OAS\Utils\Helper;

class AddressCanada extends Address
{
    public function __construct(
        public readonly string $province,
        string $street,
        string $city,
        string $zipCode,
        ?PhoneNumber $phoneNumber = null
    )
    {
        parent::__construct(
            'CAN',
            $street,
            $city,
            $zipCode,
            $phoneNumber
        );
    }
}