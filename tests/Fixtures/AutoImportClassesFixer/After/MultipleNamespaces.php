<?php

declare(strict_types = 1);

namespace First {
    use Carbon\Carbon as CarbonCarbon;

    final class Example
    {
        public const array CLASSES = [
            CarbonCarbon::class,
            Carbon::class,
        ];
    }
}

namespace Second {
    use Domain\Collection;
    use Illuminate\Database\Eloquent\Collection as EloquentCollection;

    final class Example
    {
        public function create(): array
        {
            return [
                Collection::new(),
                EloquentCollection::new(),
            ];
        }
    }
}
