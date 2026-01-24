<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;

return register_fixers([
    ListSyntaxFixer::class => true,
]);
