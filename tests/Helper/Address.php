<?php declare(strict_types=1);

namespace Tests\OAS\Utils\Helper;

abstract class Address
{
    public function __construct(
        public readonly string $country,
        public readonly string $street,
        public readonly string $city,
        public readonly string $zipCode,
        public readonly ?PhoneNumber $phoneNumber = null
    )
    {
    }
}