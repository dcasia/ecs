<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Basic\NoMultipleStatementsPerLineFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\Basic\NumericLiteralSeparatorFixer;
use PhpCsFixer\Fixer\Basic\OctalNotationFixer;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;

return register_fixers([
    BracesPositionFixer::class => false,
    EncodingFixer::class => true,
    NoMultipleStatementsPerLineFixer::class => true,
    NonPrintableCharacterFixer::class => true,
    NoTrailingCommaInSinglelineFixer::class => [ 'elements' => [ 'arguments', 'array_destructuring', 'group_import' ] ],
    NumericLiteralSeparatorFixer::class => true,
    OctalNotationFixer::class => true,
    PsrAutoloadingFixer::class => true,
    SingleLineEmptyBodyFixer::class => false,
]);
