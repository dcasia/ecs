<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer;

use DigitalCreative\ECS\Tests\Support\ReflectionDatabaseConnection;
use DigitalCreative\ECS\Tests\Support\ReflectionMagicConnectionFacade;
use stdClass;

function reflectedConnection(): ReflectionDatabaseConnection
{
    return ReflectionMagicConnectionFacade::connection('events');
}

function queryFromFunction(ReflectionDatabaseConnection $connection, object $context): void
{
    $connection->write(
        'function query',
        [ 'context' => $context ],
        $context,
    );
}

trait QueriesFromTrait
{
    private function queryFromTrait(ReflectionDatabaseConnection $connection, object $context): void
    {
        $connection->write(
            'trait query',
            [ 'nested' => [ $context ] ],
            $context,
        );
    }
}

final class QueryService
{
    use QueriesFromTrait;

    public function run(ReflectionDatabaseConnection $connection, object $context): void
    {
        $this->queryFromTrait($connection, $context);
    }
}

$context = new stdClass();
$connection = reflectedConnection();
$magicConnection = ReflectionMagicConnectionFacade::connection('events');

$magicConnection->selectOne(
    'magic return query',
    [ $context ],
);

$connection->write(
    'top-level query',
    [ $context ],
    $context,
);

ReflectionMagicConnectionFacade::selectOne(
    'direct facade query',
    [ $context ],
);

ReflectionMagicConnectionFacade::dispatch(
    static fn (object $payload, array $metadata): object => $payload,
    [ 'objects' => [ $context ] ],
);

ReflectionMagicConnectionFacade::write(
    'trait-provided magic query',
    [ 'objects' => [ $context ] ],
    $context,
);

queryFromFunction($connection, $context);
