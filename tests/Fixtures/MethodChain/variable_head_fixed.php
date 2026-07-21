<?php

declare(strict_types = 1);

final class UserRepository
{
    public function first(Builder $builder): User
    {
        return $builder->where('active', true)
            ->first();
    }
}
