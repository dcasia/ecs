<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class RequireNamedArgumentsForMultilineCallsSniffTest extends EcsTestCase
{
    public function test_reports_positional_arguments_in_multiline_method_call(): void
    {
        $this->assertFixtureFailsWith(
            fixture: __DIR__ . '/../Fixtures/RequireNamedArgumentsForMultilineCalls/positional_method_call.php',
            messages: [
                'Argument 1 of a multiline call must be named.',
                'Argument 2 of a multiline call must be named.',
                'DigitalCreative\ECS\Sniffs\RequireNamedArgumentsForMultilineCallsSniff.Missing',
            ],
        );
    }

    public function test_reports_positional_arguments_in_multiline_function_call(): void
    {
        $this->assertFixtureFailsWith(
            fixture: __DIR__ . '/../Fixtures/RequireNamedArgumentsForMultilineCalls/positional_function_call.php',
            messages: [
                'Argument 1 of a multiline call must be named.',
                'Argument 2 of a multiline call must be named.',
            ],
        );
    }

    public function test_reports_positional_arguments_in_multiline_constructor_call(): void
    {
        $this->assertFixtureFailsWith(
            fixture: __DIR__ . '/../Fixtures/RequireNamedArgumentsForMultilineCalls/positional_constructor_call.php',
            messages: [
                'Argument 1 of a multiline call must be named.',
                'Argument 2 of a multiline call must be named.',
            ],
        );
    }

    public function test_accepts_single_unnamed_array_argument(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireNamedArgumentsForMultilineCalls/single_array_argument.php');
    }

    public function test_accepts_named_arguments_in_multiline_method_call(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireNamedArgumentsForMultilineCalls/named_method_call.php');
    }
}
