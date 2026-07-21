<?php

declare(strict_types = 1);

namespace App\Repositories;

use App\Models\Inquiry;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class InquiryRepository
{
    public function dispatch(Builder $query): void
    {
        $query->where('retailer_id', 1)->exists();
        Inquiry::query()->whereKey(1)->exists();

        DB::transaction(function (): void {
            $lead = new Lead();
            $lead->save();
        });
    }
}
