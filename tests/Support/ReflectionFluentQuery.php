<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Support;

final class ReflectionFluentQuery
{
    public function join(string $table, string $first, string $operator, string $second): self
    {
        return $this;
    }

    public function select(array $columns): self
    {
        return $this;
    }

    public function selectRaw(string $expression, array $bindings): self
    {
        return $this;
    }

    public function where(string $column, mixed $value): self
    {
        return $this;
    }

    public function whereRaw(string $sql, array $bindings): self
    {
        return $this;
    }
}
