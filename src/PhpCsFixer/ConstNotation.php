<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        NativeConstantInvocationFixer::class => false,
    ];

    register_fixers($config, $options);

};
