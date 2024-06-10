<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\ClassOpeningBracketFixer;
use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\AttributeNotation\AttributeEmptyParenthesesFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSpaceFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use PhpCsFixer\Fixer\Whitespace\SpacesInsideParenthesesFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withRules([
        ClassOpeningBracketFixer::class,
        //        MethodChainFixer::class,
        PaddedArrayFixer::class,
        PaddedBlockFixer::class,
        DeclareStrictTypesFixer::class,
        LinebreakAfterOpeningTagFixer::class,
        BlankLinesBeforeNamespaceFixer::class,
        BlankLineAfterNamespaceFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        WhitespaceAfterCommaInArrayFixer::class,
        ArrayIndentationFixer::class,
        ArraySyntaxFixer::class,
        AttributeEmptyParenthesesFixer::class,
        BinaryOperatorSpacesFixer::class,
        BracesPositionFixer::class,
        CastSpacesFixer::class,
        ClassAttributesSeparationFixer::class,
        ClassDefinitionFixer::class,
        CompactNullableTypeDeclarationFixer::class,
        ConstantCaseFixer::class,
        ControlStructureBracesFixer::class,
        ControlStructureContinuationPositionFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        FunctionDeclarationFixer::class,
        GlobalNamespaceImportFixer::class,
        IndentationTypeFixer::class,
        LineEndingFixer::class,
        LowercaseCastFixer::class,
        LowercaseKeywordsFixer::class,
        LowercaseStaticReferenceFixer::class,
        MagicConstantCasingFixer::class,
        MagicMethodCasingFixer::class,
        MethodArgumentSpaceFixer::class,
        MethodChainingIndentationFixer::class,
        NativeFunctionCasingFixer::class,
        NativeTypeDeclarationCasingFixer::class,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        NoSpaceAroundDoubleColonFixer::class,
        NoTrailingWhitespaceFixer::class,
        NotOperatorWithSpaceFixer::class,
        NullableTypeDeclarationFixer::class,
        NullableTypeDeclarationForDefaultNullValueFixer::class,
        OperatorLinebreakFixer::class,
        OrderedImportsFixer::class,
        OrderedInterfacesFixer::class,
        OrderedTypesFixer::class,
        ProtectedToPrivateFixer::class,
        ReturnTypeDeclarationFixer::class,
        SingleQuoteFixer::class,
        SingleSpaceAroundConstructFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        VisibilityRequiredFixer::class,
        BlankLineBetweenImportGroupsFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        SingleBlankLineAtEofFixer::class,
        SpacesInsideParenthesesFixer::class,
        StatementIndentationFixer::class,
        TypeDeclarationSpacesFixer::class,
    ])
    ->withConfiguredRule(BlankLineBeforeStatementFixer::class, [
        'statements' => [
            'break', 'case', 'continue', 'declare', 'default', 'do', 'exit', 'for', 'foreach', 'goto', 'if',
            'include', 'include_once', 'phpdoc', 'require', 'require_once', 'return', 'switch', 'throw',
            'try', 'while', 'yield', 'yield_from',
        ],
    ])
    ->withConfiguredRule(ConcatSpaceFixer::class, [ 'spacing' => 'one' ])
    ->withConfiguredRule(DeclareEqualNormalizeFixer::class, [ 'space' => 'single' ])
    ->withConfiguredRule(NoSpacesAroundOffsetFixer::class, [ 'positions' => [ 'outside' ] ])
    ->withConfiguredRule(TypesSpacesFixer::class, [ 'space' => 'single' ]);
