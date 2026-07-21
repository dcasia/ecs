<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class FunctionOpeningBracketFixerTest extends EcsTestCase
{
    public function test_moves_multiline_constructor_bracket_to_next_line(): void
    {
        $this->assertFixtureIsFixedToUsingConfig(
            inputFixture: __DIR__ . '/../Fixtures/FunctionOpeningBracket/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/FunctionOpeningBracket/fixed.php',
            config: __DIR__ . '/../Support/FunctionOpeningBracket.php',
        );
    }

    public function test_leaves_anonymous_function_bracket_on_signature_line(): void
    {
        $this->assertFixturePassesUsingConfig(
            fixture: __DIR__ . '/../Fixtures/FunctionOpeningBracket/anonymous_function.php',
            config: __DIR__ . '/../Support/FunctionOpeningBracket.php',
        );
    }
}
