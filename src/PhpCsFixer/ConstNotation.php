<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;

return register_fixers([
    NativeConstantInvocationFixer::class => false,
]);

