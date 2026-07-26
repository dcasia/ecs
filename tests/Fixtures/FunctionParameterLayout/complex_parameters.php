<?php

declare(strict_types = 1);

final class ComplexParameters
{
    public static function configured(
        array $options = [
            'enabled' => true,
        ],
    ): self
    {
        return new self();
    }

    public static function attributed(
        #[MapInputName('definition')]
        array $definition,
    ): self
    {
        return new self();
    }
}
