<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        NoHomoglyphNamesFixer::class => true,
    ];

    register_fixers($config, $options);

};
