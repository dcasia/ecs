<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeTypeDeclarationCasingFixer;

return register_fixers([
    ClassReferenceNameCasingFixer::class => true,
    ConstantCaseFixer::class => true,
    IntegerLiteralCaseFixer::class => true,
    LowercaseKeywordsFixer::class => true,
    LowercaseStaticReferenceFixer::class => true,
    MagicConstantCasingFixer::class => true,
    MagicMethodCasingFixer::class => true,
    NativeFunctionCasingFixer::class => true,
//    NativeFunctionTypeDeclarationCasingFixer::class => true,
    NativeTypeDeclarationCasingFixer::class => true,
]);

