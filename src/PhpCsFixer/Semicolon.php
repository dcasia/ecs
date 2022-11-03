<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        MultilineWhitespaceBeforeSemicolonsFixer::class => true,
        NoEmptyStatementFixer::class => true,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class => true,
        SemicolonAfterInstructionFixer::class => true,
        SpaceAfterSemicolonFixer::class => true,
    ];

    register_fixers($config, $options);

};
