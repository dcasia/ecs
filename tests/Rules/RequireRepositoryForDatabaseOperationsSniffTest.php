<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Rules;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class RequireRepositoryForDatabaseOperationsSniffTest extends EcsTestCase
{
    public function test_reports_database_operations_outside_repository_in_laravel(): void
    {
        $this->assertFixtureFailsWithUsingConfig(
            fixture: __DIR__ . '/../Fixtures/RequireRepositoryForDatabaseOperations/Inquiry.php',
            messages: [
                'Database operation `where()` is outside a repository.',
                'Database operation `query()` is outside a repository.',
                'Database operation `transaction()` is outside a repository.',
                'Database operation `save()` is outside a repository.',
                'DB queries MUST be done via a repository class.',
                'filename ends in `Repository.php`',
                'own Eloquent model',
                'delegate interactions with other models',
                'respective repositories.',
                'DigitalCreative\ECS\Sniffs\RequireRepositoryForDatabaseOperationsSniff.OutsideRepository',
            ],
            config: __DIR__ . '/../Support/LaravelRepository.php',
        );
    }

    public function test_reports_queries_on_imported_models_in_laravel(): void
    {
        $this->assertFixtureFailsWithUsingConfig(
            fixture: __DIR__ . '/../Fixtures/RequireRepositoryForDatabaseOperations/UserController.php',
            messages: [ 'Database operation `query()` is outside a repository.' ],
            config: __DIR__ . '/../Support/LaravelRepository.php',
        );
    }

    public function test_accepts_database_operations_inside_repository_file(): void
    {
        $this->assertFixturePassesUsingConfig(
            fixture: __DIR__ . '/../Fixtures/RequireRepositoryForDatabaseOperations/InquiryRepository.php',
            config: __DIR__ . '/../Support/LaravelRepository.php',
        );
    }

    public function test_remains_disabled_without_laravel(): void
    {
        $this->assertFixturePasses(__DIR__ . '/../Fixtures/RequireRepositoryForDatabaseOperations/UserController.php');
    }
}
