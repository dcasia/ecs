<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class PaddedMultilineAssignmentFixerTest extends EcsTestCase
{
    public function test_pads_multiline_assignments_with_blank_lines(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/PaddedMultilineAssignment/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/PaddedMultilineAssignment/fixed.php',
        );
    }

    public function test_keeps_consecutive_single_line_assignments_compact(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/PaddedMultilineAssignment/single_line.php');
    }

    public function test_does_not_treat_for_loop_clauses_as_assignments(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/PaddedMultilineAssignment/for_loop.php');
    }
}
