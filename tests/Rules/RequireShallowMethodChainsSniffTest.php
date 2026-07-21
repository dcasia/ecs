<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class RequireShallowMethodChainsSniffTest extends EcsTestCase
{
    public function test_reports_method_chains_nested_beyond_three_levels(): void
    {
        $this->assertFixtureFailsWith(
            fixture: __DIR__ . '/../Fixtures/RequireShallowMethodChains/deep.php',
            messages: [
                'Nested method chain reaches level 4; the maximum is 3.',
                'Extract the chain passed to `icons()` into a named method',
            ],
        );
    }

    public function test_accepts_method_chains_nested_to_exactly_three_levels(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireShallowMethodChains/three_levels.php');
    }

    public function test_accepts_an_extracted_nested_chain(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireShallowMethodChains/extracted.php');
    }
}
