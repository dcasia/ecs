<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class CompactFunctionParametersFixerTest extends EcsTestCase
{
    public function test_compacts_simple_signatures_with_at_most_three_parameters(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/CompactFunctionParameters/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/CompactFunctionParameters/fixed.php',
        );
    }

    public function test_keeps_signatures_with_four_parameters_multiline(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/CompactFunctionParameters/four_parameters.php');
    }

    public function test_keeps_multiline_default_values_expanded(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/CompactFunctionParameters/multiline_default.php');
    }
}
