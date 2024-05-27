<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoBlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;

return register_fixers([
    BlankLineAfterNamespaceFixer::class => true,
    BlankLinesBeforeNamespaceFixer::class => true,
    CleanNamespaceFixer::class => true,
//    NoBlankLinesBeforeNamespaceFixer::class => false,
    NoLeadingNamespaceWhitespaceFixer::class => true,
//    SingleBlankLineBeforeNamespaceFixer::class => true,
]);

