<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\AutoImportClassesFixer;

use App\Support\CarbonCarbon;

final class AliasCollision
{
    public const array SERIALIZABLE_CLASSES = [
        CarbonCarbon::class,
        \Carbon\Carbon::class,
        Carbon::class,
    ];
}
