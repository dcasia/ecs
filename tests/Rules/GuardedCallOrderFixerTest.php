<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class GuardedCallOrderFixerTest extends EcsTestCase
{
    public function test_moves_non_null_guard_before_multiline_call_using_guarded_variable(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/GuardedCallOrder/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/GuardedCallOrder/fixed.php',
        );
    }

    public function test_orders_guard_after_extracting_multiline_if_condition(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/GuardedCallOrder/if_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/GuardedCallOrder/if_fixed.php',
        );
    }

    public function test_does_not_move_guard_for_unrelated_variable(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/GuardedCallOrder/unrelated_guard.php');
    }
}
