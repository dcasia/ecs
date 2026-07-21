<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;

return register_fixers([
    BlankLineAfterNamespaceFixer::class => true,
    BlankLinesBeforeNamespaceFixer::class => true,
    CleanNamespaceFixer::class => true,
    NoLeadingNamespaceWhitespaceFixer::class => true,
]);
