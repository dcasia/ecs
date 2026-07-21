<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class MethodChainFixerTest extends EcsTestCase
{
    public function test_breaks_entire_chain_and_compacts_short_array_argument(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/multiline_array_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/multiline_array_fixed.php',
        );
    }

    public function test_keeps_long_array_argument_multiline_when_breaking_chain(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/long_array_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/long_array_fixed.php',
        );
    }

    public function test_attaches_simple_first_member_to_this_receiver(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/simple_head_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/simple_head_fixed.php',
        );
    }

    public function test_attaches_simple_first_member_to_variable_receiver(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/variable_head_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/variable_head_fixed.php',
        );
    }

    public function test_moves_first_member_below_an_expanded_static_constructor(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/static_constructor_head_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/static_constructor_head_fixed.php',
        );
    }

    public function test_keeps_chain_inline_when_only_final_member_is_multiline(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/MethodChain/final_multiline_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/MethodChain/final_multiline_fixed.php',
        );
    }

    public function test_keeps_single_operator_multiline_calls_attached_to_receiver(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/MethodChain/single_operator_multiline_call.php');
    }

    public function test_preserves_an_expanded_chain_when_joining_it_would_exceed_the_line_limit(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/MethodChain/expanded_overlong_final_call.php');
    }

    public function test_keeps_compact_chains_on_one_line(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/MethodChain/single_line.php');
    }
}
