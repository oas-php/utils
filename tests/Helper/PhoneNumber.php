<?php declare(strict_types=1);

namespace Tests\OAS\Utils\Helper;

class PhoneNumber
{
    public function __construct(
        public readonly int $number,
        public readonly int $countryCode = 1
    ) {
    }
}