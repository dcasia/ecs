<?php

declare(strict_types = 1);

namespace App\Filament;

use Illuminate\Database\Eloquent\Builder;

final class NamedMethodCallFixture
{
    public function build(callable $get): mixed
    {
        return User::query()->when(
            value: $get('retailer_id') ?? auth()->user()?->retailer_id,
            callback: fn (Builder $query, int | string $retailerId) => $query->where('retailer_id', $retailerId),
        );
    }
}
