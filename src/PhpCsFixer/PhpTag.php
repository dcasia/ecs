<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        BlankLineAfterOpeningTagFixer::class => true,
        EchoTagSyntaxFixer::class => true,
        FullOpeningTagFixer::class => true,
        LinebreakAfterOpeningTagFixer::class => true,
        NoClosingTagFixer::class => true,
    ];

    register_fixers($config, $options);

};
