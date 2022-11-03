<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\StringNotation\EscapeImplicitBackslashesFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\StringNotation\StringLengthToEmptyFixer;
use PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        EscapeImplicitBackslashesFixer::class => false,
        ExplicitStringVariableFixer::class => false,
        HeredocToNowdocFixer::class => false,
        NoBinaryStringFixer::class => true,
        NoTrailingWhitespaceInStringFixer::class => true,
        SimpleToComplexStringVariableFixer::class => true,
        SingleQuoteFixer::class => true,
        StringLengthToEmptyFixer::class => true,
        StringLineEndingFixer::class => true,
    ];

    register_fixers($config, $options);

};
