<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class FunctionParameterLayoutFixerTest extends EcsTestCase
{
    public function provideConfig(): string
    {
        return __DIR__ . '/../Support/FunctionParameterLayout.php';
    }

    public function test_expands_constructors_and_compacts_short_functions(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/fixed.php',
        );
    }

    public function test_compacts_simple_body_constructors_including_promoted_properties(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/body_constructor_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/body_constructor_fixed.php',
        );
    }

    public function test_places_the_opening_brace_after_a_compacted_signature(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/opening_brace_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/opening_brace_fixed.php',
        );
    }

    public function test_keeps_functions_with_many_parameters_expanded(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/many_parameters.php',
        );
    }

    public function test_keeps_complex_parameters_expanded(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/complex_parameters.php',
        );
    }

    public function test_keeps_overlong_signatures_expanded(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/../Fixtures/FunctionParameterLayout/long_signature.php',
        );
    }
}
