<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Comment\CommentToPhpdocFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    $options = [
        CommentToPhpdocFixer::class => true,
        HeaderCommentFixer::class => false,
        MultilineCommentOpeningClosingFixer::class => true,
        NoEmptyCommentFixer::class => true,
        NoTrailingWhitespaceInCommentFixer::class => true,
        SingleLineCommentStyleFixer::class => true,
    ];

    register_fixers($configurator, $options);

};
