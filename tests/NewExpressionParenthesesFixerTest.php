<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class NewExpressionParenthesesFixerTest extends EcsTestCase
{
    public function test_parentheses_are_removed_from_chained_new_expressions(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/NewExpressionParenthesesFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/Example.php',
            $fixtureDirectory . '/After/Example.php',
        );
    }
}
