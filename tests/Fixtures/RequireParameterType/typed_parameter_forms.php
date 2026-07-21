<?php

declare(strict_types = 1);

function typedFunction(string $functionParameter): void
{
}

final class ParameterTypesFixture
{
    public function __construct(
        public mixed $promotedParameter,
    )
    {
    }

    public function typedMethod(int &$methodParameter): void
    {

        $closure = function (object $closureParameter): void {
        };

        $arrow = fn (mixed ...$variadicParameter): array => $variadicParameter;
    }
}
