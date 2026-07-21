<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class MethodSeparationTest extends EcsTestCase
{
    public function test_adds_blank_line_between_methods(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodSeparation/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodSeparation/fixed.php',
        );
    }
}
