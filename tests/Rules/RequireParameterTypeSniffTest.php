<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class RequireParameterTypeSniffTest extends EcsTestCase
{
    public function test_reports_untyped_arrow_function_parameter(): void
    {
        $this->assertFixtureFailsWith(
            __DIR__ . '/../Fixtures/RequireParameterType/untyped_arrow_function.php',
            'Parameter $query must have a native type declaration.',
            'DigitalCreative\ECS\Sniffs\RequireParameterTypeSniff.Missing',
        );
    }

    public function test_reports_every_untyped_parameter_form(): void
    {
        $this->assertFixtureFailsWith(
            __DIR__ . '/../Fixtures/RequireParameterType/untyped_parameter_forms.php',
            'Parameter $functionParameter must have a native type declaration.',
            'Parameter $promotedParameter must have a native type declaration.',
            'Parameter $methodParameter must have a native type declaration.',
            'Parameter $closureParameter must have a native type declaration.',
            'Parameter $variadicParameter must have a native type declaration.',
        );
    }

    public function test_accepts_explicit_native_types_including_mixed(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireParameterType/typed_parameter_forms.php');
    }
}
