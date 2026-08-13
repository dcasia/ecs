<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer;

use DigitalCreative\ECS\Tests\Support\ReflectionPestAssertions;
use DigitalCreative\ECS\Tests\Support\ReflectionPestTestCase;
use LogicException;

final class Experiment
{
}

final class LandingGroupResultData
{
    public static function summary(Experiment $experiment, Experiment $definition): void
    {
    }
}

uses(ReflectionPestTestCase::class, ReflectionPestAssertions::class);

test('Pest binds the configured test case and traits to the closure', function (): void {

    $invalidDefinition = new Experiment();

    $this->assertThrows(
        test: fn () => LandingGroupResultData::summary(new Experiment(), $invalidDefinition),
        expectedClass: LogicException::class,
        expectedMessage: 'Only a configured landing group',
    );

    $this->assertResourceAccess(
        resource: new Experiment(),
        ability: 'read',
    );

});
