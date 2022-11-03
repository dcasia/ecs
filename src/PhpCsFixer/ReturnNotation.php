<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        NoUselessReturnFixer::class => true,
        ReturnAssignmentFixer::class => true,
        SimplifiedNullReturnFixer::class => false,
    ];

    register_fixers($config, $options);

};
