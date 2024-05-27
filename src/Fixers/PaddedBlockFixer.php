<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use Exception;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Throwable;

final class PaddedBlockFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'This fixer improves on the readability of PSR-12 by adding a negative space between blocks (if/else/while etc..) making it clearer and easier to read and understand.',
            [],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([
            T_IF, T_ELSE, T_FOR, T_FOREACH, T_WHILE, T_DO, T_TRY, T_CATCH, T_ELSEIF, T_FUNCTION,
        ]);
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @throws Exception
     */
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; $index++) {

            $token = $this->token($tokens, $index);

            if ($token->isGivenKind([ T_IF, T_ELSE, T_FOR, T_FOREACH, T_WHILE, T_DO, T_TRY, T_CATCH, T_ELSEIF ])) {

                $this->fixBlock($tokens, $index);

            } else if ($token->isGivenKind([ T_FUNCTION ])) {

                /**
                 * Anonymous function
                 */
                if ($this->token($tokens, $tokens->getNextMeaningfulToken($index))->equals('(')) {

                    $this->fixBlock($tokens, $index);

                } else {

                    $this->ensureNotPadded($tokens, $index);

                }

            }

        }
    }

    /**
     * @throws Exception
     */
    private function ensureNotPadded(Tokens $tokens, int $start): void
    {
        if ($boundaries = $this->getBlockBoundaries($tokens, $start)) {

            /**
             *
             * public function name()
             * {
             *   •
             *   // body
             *   •
             * }
             */
            if ($this->countNewLines($tokens, $boundaries[ 0 ] + 1) > 1) {
                $this->removeLineAt($tokens, $boundaries[ 0 ] + 1);
            }

            if ($this->countNewLines($tokens, $boundaries[ 1 ] - 1) > 1) {
                $this->removeLineAt($tokens, $boundaries[ 1 ] - 1);
            }

        }
    }

    /**
     * @throws Exception
     */
    private function fixBlock(Tokens $tokens, int $start): void
    {
        [ $blockStartIndex, $blockEndIndex ] = $this->getBlockBoundaries($tokens, $start) ?? [ null, null ];

        if ($blockStartIndex === null || $blockEndIndex === null) {
            return;
        }

        /**
         * if() {
         *   •
         *   return
         *   •
         * }
         */
//        if ($this->token($tokens, $tokens->getNextMeaningfulToken($blockStartIndex))->isGivenKind(T_RETURN)) {
//
//            $this->unwrapNewLines($tokens, $blockStartIndex, $blockEndIndex);
//
//            return;
//
//        }

        /**
         * if/else/try/catch() {
         *   •
         *   // body
         *   •
         * }
         */
        if ($this->countLinesBetween($tokens, $blockStartIndex + 1, $blockEndIndex - 1) === 2) {

            if ($this->isMultiLevelBlock($tokens, $blockStartIndex, $blockEndIndex) === false) {

                $this->unwrapNewLines($tokens, $blockStartIndex, $blockEndIndex);

                return;

            }

        }

        /**
         * if() {
         *   •
         *   // body
         * }
         */
        if ($this->countNewLines($tokens, $blockStartIndex + 1) !== 2) {
            $this->ensureWhitespaceAtIndex($tokens, $blockStartIndex + 1);
        }

        /**
         * if() {
         *   // body
         *   •
         * }
         */
        if ($this->countNewLines($tokens, $blockEndIndex - 1) !== 2) {
            $this->ensureWhitespaceAtIndex($tokens, $blockEndIndex - 1);
        }
    }

    /**
     * @throws Exception
     */
    private function unwrapNewLines(Tokens $tokens, int $start, int $end): void
    {
        if ($this->countNewLines($tokens, $start + 1)) {
            $this->removeLineAt($tokens, $start + 1);
        }

        if ($this->countNewLines($tokens, $end - 1)) {
            $this->removeLineAt($tokens, $end - 1);
        }
    }

    private function isMultiLevelBlock(Tokens $tokens, int $start, int $end): bool
    {
        $next = $tokens->getNextMeaningfulToken($end);
        $previous = $tokens->getPrevMeaningfulToken($start);

        if ($this->token($tokens, $previous)->equals(')')) {

            $blockStart = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $previous);
            $previous = $tokens->getPrevMeaningfulToken($blockStart);

            /**
             * else if vs elseif
             */
            if ($this->token($tokens, $previous)->isGivenKind([ T_IF ])) {
                $previous = $tokens->getPrevMeaningfulToken($previous);
            }

        }

        return $this->token($tokens, $next)->isGivenKind([ T_ELSE, T_ELSEIF, T_CATCH, T_DO ])
            || $this->token($tokens, $previous)->isGivenKind([ T_ELSE, T_ELSEIF, T_CATCH, T_DO ]);
    }

    private function getBlockBoundaries(Tokens $tokens, int $start): ?array
    {
        $blockStartIndex = $tokens->getNextTokenOfKind($start, [ '{' ]);

        try {

            $blockEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $blockStartIndex);

        } catch (Throwable) {

            return null;

        }

        /**
         * If body is empty
         * public function name()
         * {
         * }
         */
        if ($blockEndIndex - $blockStartIndex <= 2) {
            return null;
        }

        return [ $blockStartIndex, $blockEndIndex ];
    }

    /**
     * @throws Exception
     */
    private function ensureWhitespaceAtIndex(Tokens $tokens, int $index): void
    {
        if ($this->token($tokens, $index)->isWhitespace() === false) {
            throw new Exception('Currently not an whitespace...');
        }

        $tokens[ $index ] = new Token(
            $this->whitespacesConfig->getLineEnding() .
            $this->token($tokens, $index)->getContent(),
        );
    }

    /**
     * @throws Exception
     */
    private function removeLineAt(Tokens $tokens, int $index): void
    {
        if ($this->token($tokens, $index)->isWhitespace() === false) {
            throw new Exception('Currently not an whitespace...');
        }

        $tokens[ $index ] = new Token(
            $this->whitespacesConfig->getLineEnding() .
            $this->getIndent($tokens, $index),
        );
    }

    private function getIndent(Tokens $tokens, int $index): string
    {
        $lines = preg_split('~(\n\s+?)~', $this->token($tokens, $index)->getContent());

        if (count($lines) === 0 || !is_array($lines)) {
            return $this->whitespacesConfig->getIndent();
        }

        while (isset($lines[ 0 ]) && $lines[ 0 ] === '') {
            array_shift($lines);
        }

        if (empty($lines)) {
            return $this->whitespacesConfig->getIndent();
        }

        return str_repeat(
            string: $this->whitespacesConfig->getIndent(),
            times: count(explode($this->whitespacesConfig->getIndent(), $lines[ 0 ])),
        );
    }

    private function countNewLines(Tokens $tokens, int $index): int
    {
        $token = $this->token($tokens, $index);

        if ($token->isWhitespace()) {
            return substr_count($token->getContent(), $this->whitespacesConfig->getLineEnding());
        }

        return 0;
    }

    private function countLinesBetween(Tokens $tokens, int $start, int $end): int
    {
        $lines = 0;

        foreach (range($start, $end) as $index) {

            if ($this->countNewLines($tokens, $index)) {
                $lines++;
            }

        }

        return $lines;
    }

    private function token(Tokens $tokens, int $index): Token
    {
        return $tokens[ $index ];
    }
}
