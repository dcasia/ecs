<?php

declare(strict_types = 1);

namespace App\Filament;

use Illuminate\Support\Collection;

final class FullyQualifiedTypeFixture
{
    public function options(): void
    {
        $options = fn (mixed $get, mixed $record): Collection => User::query()->get();
    }
}
