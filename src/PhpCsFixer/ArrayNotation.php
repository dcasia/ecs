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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    $options = [
        ArraySyntaxFixer::class => true,
        NoMultilineWhitespaceAroundDoubleArrowFixer::class => true,
        NormalizeIndexBraceFixer::class => true,
        NoTrailingCommaInSinglelineArrayFixer::class => true,
        NoWhitespaceBeforeCommaInArrayFixer::class => true,
        TrimArraySpacesFixer::class => false,
        WhitespaceAfterCommaInArrayFixer::class => true,
    ];

    register_fixers($configurator, $options);

};
