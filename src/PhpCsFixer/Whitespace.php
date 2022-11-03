<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer;
use PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesInsideParenthesisFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        ArrayIndentationFixer::class => true,
        BlankLineBeforeStatementFixer::class => [ 'statements' => [ 'do', 'for', 'foreach', 'if', 'return', 'switch', 'try', 'while', 'yield', 'yield_from' ] ],
        CompactNullableTypehintFixer::class => true,
        HeredocIndentationFixer::class => [ 'indentation' => 'same_as_start' ],
        IndentationTypeFixer::class => true,
        LineEndingFixer::class => true,
        MethodChainingIndentationFixer::class => true,
        NoExtraBlankLinesFixer::class => [ 'tokens' => [ 'extra' ] ],
        NoSpacesAroundOffsetFixer::class => [ 'positions' => [ 'outside' ] ],
        NoSpacesInsideParenthesisFixer::class => true,
        NoTrailingWhitespaceFixer::class => true,
        NoWhitespaceInBlankLineFixer::class => true,
        SingleBlankLineAtEofFixer::class => true,
        TypesSpacesFixer::class => [ 'space' => 'none' ],
    ];

    register_fixers($config, $options);

};
