<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\AttributeNotation\AttributeEmptyParenthesesFixer;
use PhpCsFixer\Fixer\AttributeNotation\OrderedAttributesFixer;

return register_fixers([
    AttributeEmptyParenthesesFixer::class => true,
    OrderedAttributesFixer::class => true,
]);

