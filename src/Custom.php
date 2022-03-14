<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS;

use DigitalCreative\ECS\Fixers\PaddedArrayFixer;
use DigitalCreative\ECS\Fixers\PaddedBlockFixer;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    $options = [
//        BracesFixer::class => true,
        PaddedArrayFixer::class => true,
    ];

    register_fixers($configurator, $options);

};
