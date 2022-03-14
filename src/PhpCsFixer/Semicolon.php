<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    $options = [
        MultilineWhitespaceBeforeSemicolonsFixer::class => true,
        NoEmptyStatementFixer::class => true,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class => true,
        SemicolonAfterInstructionFixer::class => true,
        SpaceAfterSemicolonFixer::class => true,
    ];

    register_fixers($configurator, $options);

};
