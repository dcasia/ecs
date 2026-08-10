<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class MultilineNamedArgumentsFixerTest extends EcsTestCase
{
    public function test_multiline_calls_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/MultilineCalls.php',
            expectedFixture: $fixtureDirectory . '/After/MultilineCalls.php',
        );
    }

    public function test_methods_called_on_new_objects_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/PestValidation.php',
            expectedFixture: $fixtureDirectory . '/After/PestValidation.php',
        );
    }

    public function test_fluent_method_chains_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/FluentQuery.php',
            expectedFixture: $fixtureDirectory . '/After/FluentQuery.php',
        );
    }

    public function test_typed_property_receivers_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/TypedPropertyCalls.php',
            expectedFixture: $fixtureDirectory . '/After/TypedPropertyCalls.php',
        );
    }

    public function test_typed_parameter_receivers_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/ParameterReceiverCalls.php',
            expectedFixture: $fixtureDirectory . '/After/ParameterReceiverCalls.php',
        );
    }

    public function test_locally_assigned_object_receivers_use_resolved_parameter_names(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/LocalVariableCalls.php',
            expectedFixture: $fixtureDirectory . '/After/LocalVariableCalls.php',
        );
    }

    public function test_local_type_inference_covers_aliases_factories_clones_and_invokable_objects(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/InferredReceiverMatrix.php',
            expectedFixture: $fixtureDirectory . '/After/InferredReceiverMatrix.php',
        );
    }

    public function test_namespace_alias_and_source_inheritance_are_resolved(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer';

        $this->assertFixtureIsFixedTo(
            inputFixture: $fixtureDirectory . '/Before/NamespaceResolution.php',
            expectedFixture: $fixtureDirectory . '/After/NamespaceResolution.php',
        );
    }

    public function test_already_named_multiline_calls_are_idempotent(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/After/MultilineCalls.php',
        );
    }

    public function test_inferred_receiver_output_is_idempotent(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/After/InferredReceiverMatrix.php',
        );
    }

    public function test_inline_first_and_single_argument_calls_are_left_unchanged(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/Valid/PartiallyMultilineCalls.php',
        );
    }

    public function test_unresolvable_variadic_and_unpacked_calls_are_left_unchanged(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/Valid/UnresolvableCalls.php',
        );
    }

    public function test_ambiguous_conditional_union_and_unknown_assignments_are_left_unchanged(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/Valid/AmbiguousLocalReceivers.php',
        );
    }
}
