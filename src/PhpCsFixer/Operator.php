<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\PhpCsFixer;

use PhpCsFixer\Fixer\Operator\AssignNullCoalescingToCoalesceEqualFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Operator\LongToShorthandOperatorFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\NoUselessConcatOperatorFixer;
use PhpCsFixer\Fixer\Operator\NoUselessNullsafeOperatorFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;

return register_fixers([
    AssignNullCoalescingToCoalesceEqualFixer::class => false,
    BinaryOperatorSpacesFixer::class => true,
    ConcatSpaceFixer::class => [ 'spacing' => 'one' ],
    IncrementStyleFixer::class => [ 'style' => 'post' ],
    LogicalOperatorsFixer::class => true,
    LongToShorthandOperatorFixer::class => true,
//    NewWithBracesFixer::class => true,
    NewWithParenthesesFixer::class => true,
    NoSpaceAroundDoubleColonFixer::class => true,
    NotOperatorWithSpaceFixer::class => false,
    NotOperatorWithSuccessorSpaceFixer::class => false,
    NoUselessConcatOperatorFixer::class => true,
    NoUselessNullsafeOperatorFixer::class => true,
    ObjectOperatorWithoutWhitespaceFixer::class => true,
    OperatorLinebreakFixer::class => true,
    StandardizeIncrementFixer::class => true,
    StandardizeNotEqualsFixer::class => true,
    TernaryOperatorSpacesFixer::class => true,
    TernaryToElvisOperatorFixer::class => true,
    TernaryToNullCoalescingFixer::class => true,
    UnaryOperatorSpacesFixer::class => true,
]);
