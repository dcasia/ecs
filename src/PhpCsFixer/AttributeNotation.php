<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\AttributeNotation\AttributeBlockNoSpacesFixer;
use PhpCsFixer\Fixer\AttributeNotation\AttributeEmptyParenthesesFixer;
use PhpCsFixer\Fixer\AttributeNotation\GeneralAttributeRemoveFixer;
use PhpCsFixer\Fixer\AttributeNotation\OrderedAttributesFixer;

return register_fixers([
    AttributeBlockNoSpacesFixer::class => true,
    AttributeEmptyParenthesesFixer::class => true,
    GeneralAttributeRemoveFixer::class => false,
    OrderedAttributesFixer::class => false,
]);
