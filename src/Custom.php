<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\ClassOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\FunctionOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\LaravelEmptyToBlankFixer;
use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use DigitalCreative\ECS\Fixers\PaddedMultilineAssignmentFixer;
use DigitalCreative\ECS\Sniffs\RequireNamedArgumentsForMultilineCallsSniff;
use DigitalCreative\ECS\Sniffs\RequireParameterTypeSniff;
use DigitalCreative\ECS\Sniffs\RequireRepositoryForDatabaseOperationsSniff;

return register_fixers([
    PaddedArrayFixer::class => true,
    PaddedBlockFixer::class => true,
    PaddedMultilineAssignmentFixer::class => true,
    ClassOpeningBracketFixer::class => true,
    FunctionOpeningBracketFixer::class => true,
    LaravelEmptyToBlankFixer::class => true,
    RequireNamedArgumentsForMultilineCallsSniff::class => true,
    RequireRepositoryForDatabaseOperationsSniff::class => true,
    RequireParameterTypeSniff::class => true,
])->withSets([
    __DIR__ . '/PhpCsFixer/Alias.php',
    __DIR__ . '/PhpCsFixer/AttributeNotation.php',
    __DIR__ . '/PhpCsFixer/ArrayNotation.php',
    __DIR__ . '/PhpCsFixer/Basic.php',
    __DIR__ . '/PhpCsFixer/Casing.php',
    __DIR__ . '/PhpCsFixer/CastNotation.php',
    __DIR__ . '/PhpCsFixer/ClassNotation.php',
    __DIR__ . '/PhpCsFixer/Comment.php',
    __DIR__ . '/PhpCsFixer/ConstNotation.php',
    __DIR__ . '/PhpCsFixer/ControlStructure.php',
    __DIR__ . '/PhpCsFixer/FunctionNotation.php',
    __DIR__ . '/PhpCsFixer/Import.php',
    __DIR__ . '/PhpCsFixer/LanguageConstruct.php',
    __DIR__ . '/PhpCsFixer/ListNotation.php',
    __DIR__ . '/PhpCsFixer/NamespaceNotation.php',
    __DIR__ . '/PhpCsFixer/Naming.php',
    __DIR__ . '/PhpCsFixer/Operator.php',
    __DIR__ . '/PhpCsFixer/Phpdoc.php',
    __DIR__ . '/PhpCsFixer/PhpTag.php',
    __DIR__ . '/PhpCsFixer/ReturnNotation.php',
    __DIR__ . '/PhpCsFixer/Semicolon.php',
    __DIR__ . '/PhpCsFixer/Strict.php',
    __DIR__ . '/PhpCsFixer/StringNotation.php',
    __DIR__ . '/PhpCsFixer/Whitespace.php',
]);
