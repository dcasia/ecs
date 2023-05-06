<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        PaddedArrayFixer::class => true,
        PaddedBlockFixer::class => true
    ];

    register_fixers($config, $options);

};
