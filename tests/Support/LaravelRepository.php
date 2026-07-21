<?php

declare(strict_types = 1);

use DigitalCreative\ECS\Sniffs\RequireRepositoryForDatabaseOperationsSniff;

require_once __DIR__ . '/Application.php';

return register_fixers([
    RequireRepositoryForDatabaseOperationsSniff::class => true,
]);
