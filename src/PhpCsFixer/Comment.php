<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Comment\CommentToPhpdocFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;

return register_fixers([
    CommentToPhpdocFixer::class => true,
    HeaderCommentFixer::class => false,
    MultilineCommentOpeningClosingFixer::class => true,
    NoEmptyCommentFixer::class => true,
    NoTrailingWhitespaceInCommentFixer::class => true,
    SingleLineCommentSpacingFixer::class => true,
    SingleLineCommentStyleFixer::class => true,
]);
