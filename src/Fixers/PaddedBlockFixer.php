<?php

declare (strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use const T_WHITESPACE;

final class PaddedBlockFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Arrays should always have a space between start and ending brackets.',
            [
                new CodeSample("<?php\n\$sample = [ 1,2,3 ];"),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 36;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $c = $tokens->count(); $index < $c; ++$index) {

            if ($tokens[ $index ]->equals('}')) {
                $this->fixArray($tokens[ $index ], $index, $tokens);
            }

        }
    }

    private function fixArray(Token $blockEndToken, int $blockEndIndex, Tokens $tokens)
    {
        /**
         * @var Token $blockEndToken
         */
        $blockStartIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_CURLY_BRACE, $blockEndIndex);
        $blockStartToken = $tokens[ $blockStartIndex ];

        $newLine = $this->whitespacesConfig->getLineEnding() . $this->whitespacesConfig->getIndent();

        [ $innerStart, $innerEnd ] = [ $blockStartIndex + 1, $blockEndIndex - 1 ];

        $firstMeaningfulToken = $this->token(
            $tokens, $firstMeaningfulTokenIndex = $tokens->getNextMeaningfulToken($blockStartIndex)
        );

        /**
         * If previous token before the first meaningful token is a newline ignore.
         *
         * if() {
         *   •
         *   $firstMeaningfulToken = true
         * }
         */
        if ($this->token($tokens, $firstMeaningfulTokenIndex - 1)->isWhitespace($newLine)) {
           return;
        }

        $lines = $this->countLinesBetween($tokens, $innerStart + 1, $innerEnd - 1);

        if ($lines > 0) {

            $tokens->insertAt(
                $innerStart + 1, new Token([ T_WHITESPACE, $this->whitespacesConfig->getLineEnding() ])
            );

        }

        return;

        dd($lines);
        dd($tokens[ $innerStart + 1 ], $tokens[ $innerEnd - 1 ]);

        if ($tokens[ $blockStartIndex + 1 ]->isWhitespace($newLine)) {

//            $tokens[ $blockStartIndex + 2 ];

        }

        if ($tokens[ $blockEndIndex - 1 ]->isWhitespace($newLine)) {

        }

        $lineEnding = $this->whitespacesConfig->getLineEnding();

        $newline1 = new Token([ T_WHITESPACE, $lineEnding ]);
        $newline2 = new Token([ T_WHITESPACE, $lineEnding ]);

        $tokens->insertAt($blockEndIndex, $newline1);
//        $tokens->insertAt($blockStartIndex-1, $newline2);

//        dd($blockStartIndex, $blockEndIndex);

    }

    private function countLinesBetween(Tokens $tokens, int $start, int $end): int
    {
        $newLine = $this->whitespacesConfig->getLineEnding() . $this->whitespacesConfig->getIndent();
        $lines = 0;

        foreach (range($start, $end) as $index) {

            $token = $this->token($tokens, $index);

            if ($token->isWhitespace($newLine) && Preg::match('/\\R/', $token->getContent())) {
                $lines++;
            }

        }

        return $lines;
    }

    private function token(Tokens $tokens, int $index): Token
    {
        return $tokens[ $index ];
    }

    private function getExpectedIndentAt(\PhpCsFixer\Tokenizer\Tokens $tokens, int $index): string
    {
        $index = $tokens->getPrevMeaningfulToken($index);
        $indent = $this->whitespacesConfig->getIndent();
        for ($i = $index; $i >= 0; --$i) {
            if ($tokens[ $i ]->equals(')')) {
                $i = $tokens->findBlockStart(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $i);
            }
            $currentIndent = $this->getIndentAt($tokens, $i);
            if (null === $currentIndent) {
                continue;
            }
            if ($this->currentLineRequiresExtraIndentLevel($tokens, $i, $index)) {
                return $currentIndent . $indent;
            }
            return $currentIndent;
        }
        return $indent;
    }
}
