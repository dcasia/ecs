<?php

declare(strict_types = 1);

namespace First {
    final class Example
    {
        public const array CLASSES = [
            \Carbon\Carbon::class,
            Carbon::class,
        ];
    }
}

namespace Second {
    use Domain\Collection;

    final class Example
    {
        public function create(): array
        {
            return [
                Collection::new(),
                \Illuminate\Database\Eloquent\Collection::new(),
            ];
        }
    }
}
