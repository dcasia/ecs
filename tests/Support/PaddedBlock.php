<?php

declare(strict_types = 1);

use DigitalCreative\ECS\Fixers\PaddedBlockFixer;

return register_fixers([
    PaddedBlockFixer::class => true,
]);
