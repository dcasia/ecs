<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Support;

final class ReflectionUniqueRule
{
    public function where(mixed $column, mixed $value = null): self
    {
        return $this;
    }
}
