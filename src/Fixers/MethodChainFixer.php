<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class MethodChainFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Chaining multiple methods should follow a consistent rule: either break everything in a new line, or keep everything in the same line.',
            [],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([ T_OBJECT_OPERATOR ]);
    }

    public function getPriority(): int
    {
        return 1000;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $index = 0;

        while ($start = $tokens->getNextTokenOfKind($index, [ [ T_OBJECT_OPERATOR ] ])) {

            $index = $start;

            $previousToken = $tokens[ $start - 1 ];
            $previousMeaningfulToken = $tokens[ $tokens->getPrevMeaningfulToken($start) ];

            if ($previousMeaningfulToken->equals(')') === false) {
                continue;
            }

            if ($this->isMultilineChain($tokens, $start) === false) {
                continue;
            }

            if ($previousToken->isWhitespace()) {
                continue;
            }

            $tokens->insertAt($start, [ new Token([ T_WHITESPACE, PHP_EOL ]) ]);

        }
    }

    private function isMultilineChain(Tokens $tokens, int $start): bool
    {
        $head = $start;
        $tail = $start;
        $count = 1;

        /**
         * Find forward
         */
        while (true) {

            $maybeOpenParenthesis = $tokens[ $openParenthesisIndex = $tokens->getNextMeaningfulToken($tail + 1) ];

            if ($maybeOpenParenthesis->equals('(')) {

                $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);

                $maybeObjectOperator = $tokens[ $nextObjectOperator = $tokens->getNextMeaningfulToken($closeParenthesis) ];

                if ($maybeObjectOperator->isGivenKind(T_OBJECT_OPERATOR)) {

                    $tail = $nextObjectOperator;

                    $count++;

                    continue;

                }

            }

            break;

        }

        /**
         * Find backward
         */
        while (true) {

            $maybeCloseParenthesis = $tokens[ $closeParenthesisIndex = $tokens->getPrevMeaningfulToken($head) ];

            if ($maybeCloseParenthesis->equals(')')) {

                $openParenthesis = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $closeParenthesisIndex);

                $maybeObjectOperator = $tokens[ $previousObjectOperator = $tokens->getPrevMeaningfulToken($openParenthesis - 1) ];

                if ($maybeObjectOperator->isGivenKind(T_OBJECT_OPERATOR)) {

                    $head = $previousObjectOperator;

                    $count++;

                    continue;

                }

            }

            break;

        }

        return ! ($count === 2) && $tokens->isPartialCodeMultiline($head, $tail);
    }
}
