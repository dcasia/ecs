<?php

declare(strict_types = 1);

final class ManyParameters
{
    public static function create(
        string $first,
        string $second,
        string $third,
        string $fourth,
    ): self
    {
        return new self();
    }
}
