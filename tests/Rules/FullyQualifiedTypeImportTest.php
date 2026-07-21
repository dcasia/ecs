<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class FullyQualifiedTypeImportTest extends EcsTestCase
{
    public function test_imports_fully_qualified_return_type(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/FullyQualifiedType/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/FullyQualifiedType/fixed.php',
        );
    }
}
