<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class PaddedMultilineStatementFixerTest extends EcsTestCase
{
    public function test_pads_multiline_assignments_with_blank_lines(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/fixed.php',
        );
    }

    public function test_pads_standalone_multiline_method_chains_with_blank_lines(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/multiline_method_chain_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/multiline_method_chain_fixed.php',
        );
    }

    public function test_preserves_padding_inside_anonymous_function_blocks(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/anonymous_function_chain_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/PaddedMultilineStatement/anonymous_function_chain_fixed.php',
        );
    }

    public function test_keeps_consecutive_single_line_assignments_compact(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/PaddedMultilineStatement/single_line.php');
    }

    public function test_does_not_treat_for_loop_clauses_as_assignments(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/PaddedMultilineStatement/for_loop.php');
    }
}
