<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class RequireSprintfForConcatenationSniffTest extends EcsTestCase
{
    public function test_reports_runtime_string_concatenation(): void
    {
        $this->assertFixtureFailsWith(
            fixture: __DIR__ . '/../Fixtures/RequireSprintfForConcatenation/concatenation.php',
            messages: [
                'String concatenation with `.` is not allowed.',
                'Use sprintf() with explicit placeholders',
            ],
        );
    }

    public function test_accepts_sprintf_formatting(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireSprintfForConcatenation/sprintf.php');
    }

    public function test_accepts_magic_constant_filesystem_paths(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireSprintfForConcatenation/filesystem_path.php');
    }
}
