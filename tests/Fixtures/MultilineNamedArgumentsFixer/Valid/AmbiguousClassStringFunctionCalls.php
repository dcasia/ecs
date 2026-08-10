<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer;

final class AmbiguousClassStringFunctionCalls
{
    public function exercise(object $record, object $data, string $manager): void
    {
        select_factory(FirstManager::class, SecondManager::class)->update(
            $record,
            $data,
        );

        dynamic_factory($manager)->update(
            $record,
            $data,
        );
    }
}
