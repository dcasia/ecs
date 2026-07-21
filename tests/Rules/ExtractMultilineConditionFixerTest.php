<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class ExtractMultilineConditionFixerTest extends EcsTestCase
{
    public function test_extracts_entire_condition_to_preserve_short_circuiting(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/ExtractMultilineCondition/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/ExtractMultilineCondition/fixed.php',
        );
    }

    public function test_keeps_conditions_with_single_line_calls_inline(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/ExtractMultilineCondition/single_line_call.php');
    }
}
