<?php declare(strict_types=1);

namespace OAS\Utils;

function assertTypeValid(string $type, mixed $value, string $parameter): void
{
    TypeValidator::assertValid($value, $type, "The \"$parameter\" parameter must be of $type type");
}