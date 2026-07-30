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

    public function test_unresolvable_variadic_and_unpacked_calls_are_left_unchanged(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/Fixtures/MultilineNamedArgumentsFixer/Valid/UnresolvableCalls.php',
        );
    }
}
