<?php

declare(strict_types = 1);

function untypedFunction($functionParameter): void
{
}

final class MissingParameterTypesFixture
{
    public function __construct(public $promotedParameter)
    {
    }

    public function untypedMethod($methodParameter): void
    {
        $closure = function (&$closureParameter): void {
        };

        $arrow = fn (...$variadicParameter): array => $variadicParameter;
    }
}
