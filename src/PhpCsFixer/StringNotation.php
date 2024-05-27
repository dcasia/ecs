<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\StringNotation\EscapeImplicitBackslashesFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocClosingMarkerFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\MultilineStringToHeredocFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\StringNotation\StringImplicitBackslashesFixer;
use PhpCsFixer\Fixer\StringNotation\StringLengthToEmptyFixer;
use PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer;

return register_fixers([
//    EscapeImplicitBackslashesFixer::class => false,
    ExplicitStringVariableFixer::class => false,
    HeredocClosingMarkerFixer::class => false,
    HeredocToNowdocFixer::class => false,
    MultilineStringToHeredocFixer::class => true,
    NoBinaryStringFixer::class => true,
    NoTrailingWhitespaceInStringFixer::class => true,
    SimpleToComplexStringVariableFixer::class => true,
    SingleQuoteFixer::class => true,
    StringImplicitBackslashesFixer::class => false,
    StringLengthToEmptyFixer::class => true,
    StringLineEndingFixer::class => true,
]);
