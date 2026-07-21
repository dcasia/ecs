<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class CompactFunctionParametersFixer extends AbstractFixer
{
    private const int MAXIMUM_PARAMETERS = 3;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Simple named function signatures with at most three parameters must remain on one line.',
            codeSamples: [
                new CodeSample("<?php\n\nfunction visible(\n    Definition \$definition,\n    Answers \$answers,\n    Calculations \$calculations,\n): Visibility\n{\n}\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 940;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; $index--) {

            if (!$tokens[ $index ]->isGivenKind(T_FUNCTION)) {
                continue;
            }

            $name = $tokens->getNextMeaningfulToken($index);

            if ($name === null) {
                continue;
            }

            if ($tokens[ $name ]->equals('&')) {
                $name = $tokens->getNextMeaningfulToken($name);
            }

            if ($name === null || !$tokens[ $name ]->isGivenKind(T_STRING)) {
                continue;
            }

            $openParenthesis = $tokens->getNextTokenOfKind($name, [ '(' ]);

            if ($openParenthesis === null) {
                continue;
            }

            $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);
            $layout = $this->inspectParameters($tokens, $openParenthesis, $closeParenthesis);

            if ($layout === null || $layout[ 'parameters' ] > self::MAXIMUM_PARAMETERS) {
                continue;
            }

            for ($whitespaceIndex = count($layout[ 'whitespace' ]) - 1; $whitespaceIndex >= 0; $whitespaceIndex--) {

                $whitespace = $layout[ 'whitespace' ][ $whitespaceIndex ];
                $previous = $tokens->getPrevMeaningfulToken($whitespace);
                $next = $tokens->getNextMeaningfulToken($whitespace);

                if (
                    $previous === null
                    || $next === null
                    || $previous === $openParenthesis
                    || $next === $closeParenthesis
                    || $tokens[ $next ]->equals(',')
                ) {

                    $tokens->clearAt($whitespace);

                    continue;

                }

                $tokens[ $whitespace ] = new Token([ T_WHITESPACE, ' ' ]);

            }
            $trailingComma = $tokens->getPrevMeaningfulToken($closeParenthesis);

            if ($trailingComma !== null && $tokens[ $trailingComma ]->equals(',')) {
                $tokens->clearAt($trailingComma);
            }

        }
    }

    /**
     * @return array{parameters: int, whitespace: list<int>}|null
     */
    private function inspectParameters(Tokens $tokens, int $openParenthesis, int $closeParenthesis): ?array
    {
        $commas = 0;
        $hasParameter = false;
        $whitespace = [];

        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {

            $token = $tokens[ $index ];

            if ($token->isComment()) {
                return null;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {

                $blockEnd = $tokens->findBlockEnd($block[ 'type' ], $index);

                if (
                    $block[ 'type' ] === Tokens::BLOCK_TYPE_ATTRIBUTE
                    || $this->containsLineBreak($tokens, $index, $blockEnd)
                ) {
                    return null;
                }

                $hasParameter = true;
                $index = $blockEnd;

                continue;

            }

            if ($token->equals(',')) {

                $commas++;

                continue;

            }

            if ($token->isWhitespace()) {

                if (str_contains($token->getContent(), "\n")) {
                    $whitespace[] = $index;
                }

                continue;

            }

            $hasParameter = true;

        }

        if ($whitespace === []) {
            return null;
        }

        $lastParameterToken = $tokens->getPrevMeaningfulToken($closeParenthesis);
        $hasTrailingComma = $lastParameterToken !== null && $tokens[ $lastParameterToken ]->equals(',');

        return [
            'parameters' => $hasParameter ? $commas + ($hasTrailingComma ? 0 : 1) : 0,
            'whitespace' => $whitespace,
        ];
    }

    private function containsLineBreak(Tokens $tokens, int $start, int $end): bool
    {
        for ($index = $start; $index <= $end; $index++) {

            if (str_contains($tokens[ $index ]->getContent(), "\n")) {
                return true;
            }

        }

        return false;
    }
}
