<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        ArraySyntaxFixer::class => true,
        NoMultilineWhitespaceAroundDoubleArrowFixer::class => true,
        NormalizeIndexBraceFixer::class => true,
        NoTrailingCommaInSinglelineArrayFixer::class => true,
        NoWhitespaceBeforeCommaInArrayFixer::class => true,
        TrimArraySpacesFixer::class => false,
        WhitespaceAfterCommaInArrayFixer::class => true,
    ];

    register_fixers($config, $options);

};
