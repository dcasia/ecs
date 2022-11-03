<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoBlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $options = [
        BlankLineAfterNamespaceFixer::class => true,
        CleanNamespaceFixer::class => true,
        NoBlankLinesBeforeNamespaceFixer::class => false,
        NoLeadingNamespaceWhitespaceFixer::class => true,
        SingleBlankLineBeforeNamespaceFixer::class => true,
    ];

    register_fixers($config, $options);

};
