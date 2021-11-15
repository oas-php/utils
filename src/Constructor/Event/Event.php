<?php declare(strict_types=1);

namespace OAS\Utils\Constructor\Event;

use OAS\Utils\Constructor\Constructor;

class Event
{
    private Constructor $constructor;

    public function __construct(Constructor $constructor)
    {
        $this->constructor = $constructor;
    }

    public function getConstructor(): Constructor
    {
        return $this->constructor;
    }
}
