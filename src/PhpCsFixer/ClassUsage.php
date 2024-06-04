<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ClassUsage\DateTimeImmutableFixer;

return register_fixers([
    DateTimeImmutableFixer::class => true,
]);

