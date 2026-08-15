<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer;

use DigitalCreative\ECS\Tests\Support\ReflectionRecordManager as RecordManager;

use function DigitalCreative\ECS\Tests\Support\complex_return_app;

abstract class ComplexReturnTypeClassStringCalls
{
    protected function handleRecordUpdate(RecordManager $record, array $data): RecordManager
    {
        return complex_return_app(RecordManager::class)->update(
            $record,
            LandingGroupData::fromFilament($data),
        );
    }

    protected function handleNestedUpdate(RecordManager $record, array $data): RecordManager
    {
        return $this->wrap(
            static fn (): RecordManager => complex_return_app(RecordManager::class)->update(
                $record,
                LandingGroupData::fromFilament($data),
            ),
        );
    }

    protected function wrap(\Closure $callback): mixed
    {
        return $callback();
    }
}
