<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        DeclareStrictTypesFixer::class => true,
        StrictComparisonFixer::class => true,
        StrictParamFixer::class => false,
    ];

    register_fixers($config, $options);

};
