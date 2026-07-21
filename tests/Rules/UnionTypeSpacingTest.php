<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class UnionTypeSpacingTest extends EcsTestCase
{
    public function test_adds_spaces_around_union_type_operators(): void
    {
        $this->assertFixtureIsFixedTo(
            __DIR__ . '/../Fixtures/UnionTypeSpacing/unfixed.php',
            __DIR__ . '/../Fixtures/UnionTypeSpacing/fixed.php',
        );
    }
}
