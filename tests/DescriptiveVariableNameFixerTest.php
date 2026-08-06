<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class DescriptiveVariableNameFixerTest extends EcsTestCase
{
    public function test_single_letters_and_type_related_abbreviations_are_replaced(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/DescriptiveVariableNameFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/ArrowFunctions.php',
            $fixtureDirectory . '/After/ArrowFunctions.php',
        );
    }

    public function test_references_are_renamed_without_crossing_nested_scope_boundaries(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/DescriptiveVariableNameFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/ClosureScopes.php',
            $fixtureDirectory . '/After/ClosureScopes.php',
        );
    }

    public function test_names_are_inferred_from_nullable_union_builtin_and_qualified_types(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/DescriptiveVariableNameFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/TypeInference.php',
            $fixtureDirectory . '/After/TypeInference.php',
        );
    }

    public function test_ambiguous_or_unsupported_variables_remain_unchanged(): void
    {
        $this->assertFixturePasses(
            __DIR__ . '/Fixtures/DescriptiveVariableNameFixer/Valid/UncertainVariables.php',
        );
    }
}
