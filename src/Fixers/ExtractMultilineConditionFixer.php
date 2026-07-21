<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class ExtractMultilineConditionFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'If conditions containing multiline calls must be extracted to a local boolean variable.',
            codeSamples: [
                new CodeSample("<?php\n\nif (\$id !== null && !\$repository->exists(\n    id: \$id,\n)) {\n    throw new Exception();\n}\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 1_050;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_IF);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $usedVariables = $this->collectVariables($tokens);
        $candidates = [];
        $conditionNumber = 1;

        for ($index = 0, $count = $tokens->count(); $index < $count; $index++) {

            if (!$tokens[ $index ]->isGivenKind(T_IF) || !$this->canExtractBefore($tokens, $index)) {
                continue;
            }

            $openParenthesis = $tokens->getNextMeaningfulToken($index);

            if ($openParenthesis === null || !$tokens[ $openParenthesis ]->equals('(')) {
                continue;
            }

            $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);

            if (!$this->containsMultilineCall($tokens, $openParenthesis, $closeParenthesis)) {

                $index = $closeParenthesis;

                continue;

            }

            $conditionStart = $tokens->getNextMeaningfulToken($openParenthesis);
            $conditionEnd = $tokens->getPrevMeaningfulToken($closeParenthesis);

            if (
                $conditionStart === null
                || $conditionEnd === null
                || $conditionStart >= $closeParenthesis
                || $conditionEnd <= $openParenthesis
            ) {

                continue;

            }

            do {

                $variable = '$condition' . ($conditionNumber === 1 ? '' : (string) $conditionNumber);
                $conditionNumber++;

            } while (isset($usedVariables[ $variable ]));

            $usedVariables[ $variable ] = true;

            $candidates[] = [
                'if' => $index,
                'open' => $openParenthesis,
                'close' => $closeParenthesis,
                'start' => $conditionStart,
                'end' => $conditionEnd,
                'variable' => $variable,
            ];

            $index = $closeParenthesis;

        }

        for ($index = count($candidates) - 1; $index >= 0; $index--) {
            $this->extractCondition($tokens, $candidates[ $index ]);
        }
    }

    /**
     * @return array<string, true>
     */
    private function collectVariables(Tokens $tokens): array
    {
        $variables = [];

        foreach ($tokens as $token) {

            if ($token->isGivenKind(T_VARIABLE)) {
                $variables[ $token->getContent() ] = true;
            }

        }

        return $variables;
    }

    private function canExtractBefore(Tokens $tokens, int $if): bool
    {
        $previous = $tokens->getPrevMeaningfulToken($if);

        while ($previous !== null && $tokens[ $previous ]->isComment()) {
            $previous = $tokens->getPrevMeaningfulToken($previous);
        }

        if ($previous === null || $tokens[ $previous ]->isGivenKind(T_ELSE)) {
            return false;
        }

        return $tokens[ $previous ]->equalsAny([ ';', '{', '}', ':' ])
            || $tokens[ $previous ]->isGivenKind(T_OPEN_TAG);
    }

    private function containsMultilineCall(Tokens $tokens, int $openCondition, int $closeCondition): bool
    {
        for ($index = $openCondition + 1; $index < $closeCondition; $index++) {

            if (!$tokens[ $index ]->equals('(')) {
                continue;
            }

            $callable = $tokens->getPrevMeaningfulToken($index);

            if ($callable === null || !$this->isCallableToken($tokens[ $callable ])) {
                continue;
            }

            $closeCall = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);

            if ($this->containsLineBreak($tokens, $index, $closeCall)) {
                return true;
            }

            $index = $closeCall;

        }

        return false;
    }

    private function isCallableToken(Token $token): bool
    {
        return $token->isGivenKind([ T_STRING, T_VARIABLE ]) || $token->equalsAny([ ')', ']' ]);
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

    /**
     * @param array{if: int, open: int, close: int, start: int, end: int, variable: string} $candidate
     */
    private function extractCondition(Tokens $tokens, array $candidate): void
    {
        $condition = [];

        for ($index = $candidate[ 'start' ]; $index <= $candidate[ 'end' ]; $index++) {
            $condition[] = $tokens[ $index ];
        }

        $indent = $this->findIndent($tokens, $candidate[ 'if' ]);

        $tokens->clearRange($candidate[ 'open' ] + 1, $candidate[ 'close' ] - 1);
        $tokens->insertAt(
            index: $candidate[ 'open' ] + 1,
            items: [ new Token([ T_VARIABLE, $candidate[ 'variable' ] ]) ],
        );

        $tokens->insertAt(
            index: $candidate[ 'if' ],
            items: [
                new Token([ T_VARIABLE, $candidate[ 'variable' ] ]),
                new Token([ T_WHITESPACE, ' ' ]),
                new Token('='),
                new Token([ T_WHITESPACE, ' ' ]),
                ...$condition,
                new Token(';'),
                new Token([ T_WHITESPACE, $this->whitespacesConfig->getLineEnding() . $indent ]),
            ],
        );
    }

    private function findIndent(Tokens $tokens, int $index): string
    {
        $whitespace = $index - 1;

        if ($whitespace < 0 || !$tokens[ $whitespace ]->isWhitespace()) {
            return '';
        }

        $content = $tokens[ $whitespace ]->getContent();
        $lineBreak = max((int) strrpos($content, "\n"), (int) strrpos($content, "\r"));

        return substr($content, $lineBreak + 1);
    }
}
