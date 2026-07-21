<?php

declare(strict_types = 1);

use DigitalCreative\ECS\Fixers\LaravelEmptyToBlankFixer;

require_once __DIR__ . '/Application.php';

return register_fixers([
    LaravelEmptyToBlankFixer::class => true,
]);
