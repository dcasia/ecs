<?php

declare(strict_types = 1);

namespace App\Filament;

final class FullyQualifiedTypeFixture
{
    public function options(): void
    {
        $options = fn (mixed $get, mixed $record): \Illuminate\Support\Collection => User::query()->get();
    }
}
