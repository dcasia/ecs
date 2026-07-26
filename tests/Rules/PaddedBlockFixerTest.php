<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class PaddedBlockFixerTest extends EcsTestCase
{
    public function provideConfig(): string
    {
        return __DIR__ . '/../Support/PaddedBlock.php';
    }

    public function test_adds_a_blank_line_after_completed_control_blocks(): void
    {
        $this->assertFixtureIsFixedTo(
            inputFixture: __DIR__ . '/../Fixtures/PaddedBlock/post_block_statement_unfixed.php',
            expectedFixture: __DIR__ . '/../Fixtures/PaddedBlock/post_block_statement_fixed.php',
        );
    }

    public function test_does_not_separate_linked_control_blocks(): void
    {
        $this->assertFixturePasses(
            fixture: __DIR__ . '/../Fixtures/PaddedBlock/linked_blocks.php',
        );
    }
}
