<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class LaravelEmptyToBlankFixerTest extends EcsTestCase
{
    public function test_converts_empty_checks_when_laravel_is_installed(): void
    {
        $this->assertFixtureIsFixedToUsingConfig(
            inputFixture: __DIR__ . '/../Fixtures/LaravelEmptyToBlank/unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/LaravelEmptyToBlank/fixed.php',
            config: __DIR__ . '/../Support/LaravelEmptyToBlank.php',
        );
    }

    public function test_leaves_empty_checks_unchanged_without_laravel(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/LaravelEmptyToBlank/unfixed.php');
    }
}
