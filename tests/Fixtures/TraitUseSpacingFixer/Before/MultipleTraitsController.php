<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\TraitUseSpacingFixer;

final class MultipleTraitsController
{
    use ChecksExistingAppointments;

    use AnotherTrait;
    public function __invoke(BookAppointmentRequest $request): ApiResponse
    {
        return $this->response();
    }
}
