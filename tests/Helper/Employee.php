<?php declare(strict_types=1);

namespace Tests\OAS\Utils\Helper;

class Employee
{
    /**
     * @param ?array<int, \Tests\OAS\Utils\Helper\AddressCanada|\Tests\OAS\Utils\Helper\AddressUSA> $addresses
     * @param ?iterable<int, \Tests\OAS\Utils\Helper\Employee> $subordinates
     */
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?array $addresses,
        public readonly PhoneNumber $phoneNumber,
        public readonly ?iterable $subordinates
    ) {
    }
}