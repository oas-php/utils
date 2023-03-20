<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver;

interface SubscriberInterface
{
    /**
     * a map of shape
     *  event <string> => listeners <callback[]>
     *
     * @return array
     */
    function getSubscribedEvents(): array;
}
