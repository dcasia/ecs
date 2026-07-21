<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\ClassOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\CompactFunctionParametersFixer;
use DigitalCreative\ECS\Fixers\ExtractMultilineConditionFixer;
use DigitalCreative\ECS\Fixers\FunctionOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\GuardedCallOrderFixer;
use DigitalCreative\ECS\Fixers\LaravelEmptyToBlankFixer;
use DigitalCreative\ECS\Fixers\MethodChainFixer;
use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use DigitalCreative\ECS\Fixers\PaddedMultilineStatementFixer;
use DigitalCreative\ECS\Sniffs\RequireNamedArgumentsForMultilineCallsSniff;
use DigitalCreative\ECS\Sniffs\RequireParameterTypeSniff;
use DigitalCreative\ECS\Sniffs\RequireRepositoryForDatabaseOperationsSniff;
use DigitalCreative\ECS\Sniffs\RequireShallowMethodChainsSniff;
use DigitalCreative\ECS\Sniffs\RequireSprintfForConcatenationSniff;

return register_fixers([
    PaddedArrayFixer::class => true,
    PaddedBlockFixer::class => true,
    PaddedMultilineStatementFixer::class => true,
    ClassOpeningBracketFixer::class => true,
    CompactFunctionParametersFixer::class => true,
    ExtractMultilineConditionFixer::class => true,
    FunctionOpeningBracketFixer::class => true,
    GuardedCallOrderFixer::class => true,
    LaravelEmptyToBlankFixer::class => true,
    MethodChainFixer::class => true,
    RequireNamedArgumentsForMultilineCallsSniff::class => true,
    RequireShallowMethodChainsSniff::class => true,
    RequireSprintfForConcatenationSniff::class => true,
    RequireRepositoryForDatabaseOperationsSniff::class => true,
    RequireParameterTypeSniff::class => true,
])
    ->withSets([
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
