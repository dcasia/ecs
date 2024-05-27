<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\CurlyBracesPositionFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Basic\NoMultipleStatementsPerLineFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\Basic\NumericLiteralSeparatorFixer;
use PhpCsFixer\Fixer\Basic\OctalNotationFixer;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;

return register_fixers([
//    BracesFixer::class => false,
    BracesPositionFixer::class => [
        'allow_single_line_anonymous_functions' => false,
        'allow_single_line_empty_anonymous_classes' => false,
        'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        'anonymous_functions_opening_brace' => 'same_line',
        'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        'control_structures_opening_brace' => 'same_line',
        'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
    ],
//    CurlyBracesPositionFixer::class => false,
    EncodingFixer::class => true,
    NoMultipleStatementsPerLineFixer::class => true,
    NonPrintableCharacterFixer::class => true,
    NoTrailingCommaInSinglelineFixer::class => true,
    NumericLiteralSeparatorFixer::class => [ 'strategy' => 'no_separator' ],
    OctalNotationFixer::class => true,
    PsrAutoloadingFixer::class => true,
    SingleLineEmptyBodyFixer::class => true,
]);
