<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\ClassOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\MethodChainFixer;
use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withRules([
        ClassOpeningBracketFixer::class,
//        MethodChainFixer::class,
        PaddedArrayFixer::class,
        PaddedBlockFixer::class,
    ])
    ->withSets([
        __DIR__ . '/PhpCsFixer/Alias.php',
        __DIR__ . '/PhpCsFixer/ArrayNotation.php',
        __DIR__ . '/PhpCsFixer/AttributeNotation.php',
        __DIR__ . '/PhpCsFixer/Basic.php',
        __DIR__ . '/PhpCsFixer/Casing.php',
        __DIR__ . '/PhpCsFixer/CastNotation.php',
        __DIR__ . '/PhpCsFixer/ClassNotation.php',
        __DIR__ . '/PhpCsFixer/ClassUsage.php',
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
