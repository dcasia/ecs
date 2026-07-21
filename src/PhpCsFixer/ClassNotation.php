<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalPublicMethodForAbstractClassFixer;
use PhpCsFixer\Fixer\ClassNotation\ModernSerializationMethodsFixer;
use PhpCsFixer\Fixer\ClassNotation\ModifierKeywordsFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer;
use PhpCsFixer\Fixer\ClassNotation\NoRedundantReadonlyPropertyFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\ClassNotation\PhpdocReadonlyClassCommentToKeywordFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\StaticPrivateMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\StringableForToStringFixer;

return register_fixers([
    ClassAttributesSeparationFixer::class => false,
    ClassDefinitionFixer::class => [
        'multi_line_extends_each_single_line' => true,
        'single_item_single_line' => true,
        'single_line' => true,
        'space_before_parenthesis' => true,
    ],
    FinalClassFixer::class => false,
    FinalInternalClassFixer::class => false,
    FinalPublicMethodForAbstractClassFixer::class => false,
    ModernSerializationMethodsFixer::class => false,
    ModifierKeywordsFixer::class => [ 'elements' => [ 'method', 'property' ] ],
    NoBlankLinesAfterClassOpeningFixer::class => true,
    NoNullPropertyInitializationFixer::class => false,
    NoPhp4ConstructorFixer::class => true,
    NoRedundantReadonlyPropertyFixer::class => true,
    NoUnneededFinalMethodFixer::class => true,
    OrderedClassElementsFixer::class => false,
    OrderedInterfacesFixer::class => true,
    OrderedTraitsFixer::class => true,
    OrderedTypesFixer::class => true,
    PhpdocReadonlyClassCommentToKeywordFixer::class => false,
    ProtectedToPrivateFixer::class => true,
    SelfAccessorFixer::class => false,
    SelfStaticAccessorFixer::class => true,
    SingleClassElementPerStatementFixer::class => true,
    SingleTraitInsertPerStatementFixer::class => false,
    StaticPrivateMethodFixer::class => false,
    StringableForToStringFixer::class => false,
]);
