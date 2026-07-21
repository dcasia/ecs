<?php

declare(strict_types = 1);

final class ServiceFixture
{
    public function __construct(mixed $dependency, mixed $logger)
    {
    }
}

new ServiceFixture(
    new stdClass(),
    new stdClass(),
);
