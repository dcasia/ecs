<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class Inquiry extends Model
{
    public function dispatch(Builder $query): void
    {
        $query->where('retailer_id', 1)->exists();
        Retailer::query()->whereKey(1)->exists();

        DB::transaction(function (): void {
            $lead = new Lead();
            $lead->save();
        });
    }
}
