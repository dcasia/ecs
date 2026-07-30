<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\TraitUseSpacingFixer;

final class TraitBeforeProperty
{
    use ChecksExistingAppointments;

    public ApiResponse $response;
}
