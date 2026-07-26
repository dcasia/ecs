<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\IndentationTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class FunctionParameterLayoutFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    use IndentationTrait;

    private const string CONSTRUCTOR_NAME = '__construct';

    private const int MAXIMUM_INLINE_PARAMETERS = 3;

    private const int MAXIMUM_LINE_LENGTH = 120;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Empty-body constructors use multiline parameters while simple body constructors and short named functions use one line.',
            codeSamples: [
                new CodeSample("<?php\n\nfinal class Example\n{\n    public function __construct(public readonly string \$name)\n    {\n    }\n\n    public static function create(\n        string \$name,\n    ): self\n    {\n    }\n}\n"),
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

            if ($tokens[ $index ]->isGivenKind(T_FUNCTION) === false) {
                continue;
            }

            $nameIndex = $this->findFunctionName($tokens, $index);

            if ($nameIndex === null) {
                continue;
            }

            $openParenthesis = $tokens->getNextTokenOfKind($nameIndex, [ '(' ]);

            if ($openParenthesis === null) {
                continue;
            }

            $closeParenthesis = $tokens->findBlockEnd(
                Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
                $openParenthesis,
            );

            if ($tokens[ $nameIndex ]->equals([ T_STRING, self::CONSTRUCTOR_NAME ], false)) {

                if ($this->hasNonEmptyBody($tokens, $closeParenthesis)
                    && $this->compactFunctionParameters($tokens, $openParenthesis, $closeParenthesis)) {
                    continue;
                }

                $this->expandConstructorParameters(
                    $tokens,
                    $index,
                    $openParenthesis,
                    $closeParenthesis,
                );

                continue;

            }

            $this->compactFunctionParameters($tokens, $openParenthesis, $closeParenthesis);

        }
    }

    private function findFunctionName(Tokens $tokens, int $functionIndex): ?int
    {
        $nameIndex = $tokens->getNextMeaningfulToken($functionIndex);

        if ($nameIndex === null) {
            return null;
        }

        if ($tokens[ $nameIndex ]->equals('&')) {
            $nameIndex = $tokens->getNextMeaningfulToken($nameIndex);
        }

        if ($nameIndex === null || $tokens[ $nameIndex ]->isGivenKind(T_STRING) === false) {
            return null;
        }

        return $nameIndex;
    }

    private function hasNonEmptyBody(Tokens $tokens, int $closeParenthesis): bool
    {
        $openBrace = $tokens->getNextTokenOfKind($closeParenthesis, [ '{', ';' ]);

        if ($openBrace === null || $tokens[ $openBrace ]->equals('{') === false) {
            return false;
        }

        $closeBrace = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openBrace);
        $firstBodyToken = $tokens->getNextMeaningfulToken($openBrace);

        return $firstBodyToken !== null && $firstBodyToken !== $closeBrace;
    }

    private function expandConstructorParameters(
        Tokens $tokens,
        int $functionIndex,
        int $openParenthesis,
        int $closeParenthesis,
    ): void {
        $firstParameter = $tokens->getNextMeaningfulToken($openParenthesis);

        if ($firstParameter === null || $firstParameter === $closeParenthesis) {
            return;
        }

        $separatorCommas = $this->findTopLevelCommas($tokens, $openParenthesis, $closeParenthesis);
        $trailingComma = $tokens->getPrevMeaningfulToken($closeParenthesis);

        if ($trailingComma === null || $tokens[ $trailingComma ]->equals(',') === false) {

            $tokens->insertAt($closeParenthesis, new Token(','));

            $trailingComma = $closeParenthesis;

        }

        $indentation = $this->getLineIndentation($tokens, $functionIndex);
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        $parameterIndentation = sprintf('%s%s', $indentation, $this->whitespacesConfig->getIndent());

        $this->setWhitespaceAfter(
            $tokens,
            $trailingComma,
            sprintf('%s%s', $lineEnding, $indentation),
        );

        for ($index = count($separatorCommas) - 1; $index >= 0; $index--) {

            $comma = $separatorCommas[ $index ];

            if ($comma === $trailingComma) {
                continue;
            }

            $this->setWhitespaceAfter(
                $tokens,
                $comma,
                sprintf('%s%s', $lineEnding, $parameterIndentation),
            );

        }

        $this->setWhitespaceAfter(
            $tokens,
            $openParenthesis,
            sprintf('%s%s', $lineEnding, $parameterIndentation),
        );
    }

    private function setWhitespaceAfter(Tokens $tokens, int $index, string $content): void
    {
        $whitespaceIndex = $index + 1;

        if ($tokens[ $whitespaceIndex ]->isWhitespace()) {

            $tokens[ $whitespaceIndex ] = new Token([ T_WHITESPACE, $content ]);

            return;

        }

        $tokens->insertAt($whitespaceIndex, new Token([ T_WHITESPACE, $content ]));
    }

    private function compactFunctionParameters(Tokens $tokens, int $openParenthesis, int $closeParenthesis): bool
    {
        $layout = $this->inspectSimpleParameters($tokens, $openParenthesis, $closeParenthesis);

        if ($layout === null || $layout[ 'parameters' ] > self::MAXIMUM_INLINE_PARAMETERS) {
            return false;
        }

        $compactedParameterLength = $this->getCompactedParameterLength(
            $tokens,
            $openParenthesis,
            $closeParenthesis,
        );

        $signatureLength = $this->getLineLengthThrough($tokens, $openParenthesis)
            + $compactedParameterLength
            + $this->getLineLengthFrom($tokens, $closeParenthesis);

        if ($signatureLength > self::MAXIMUM_LINE_LENGTH) {
            return false;
        }

        for ($index = count($layout[ 'whitespace' ]) - 1; $index >= 0; $index--) {

            $whitespaceIndex = $layout[ 'whitespace' ][ $index ];
            $previous = $tokens->getPrevMeaningfulToken($whitespaceIndex);
            $next = $tokens->getNextMeaningfulToken($whitespaceIndex);

            if ($previous === null
                || $next === null
                || $previous === $openParenthesis
                || $next === $closeParenthesis
                || $tokens[ $next ]->equals(',')) {

                $tokens->clearAt($whitespaceIndex);

                continue;

            }

            $tokens[ $whitespaceIndex ] = new Token([ T_WHITESPACE, ' ' ]);

        }

        $trailingComma = $tokens->getPrevMeaningfulToken($closeParenthesis);

        if ($trailingComma !== null && $tokens[ $trailingComma ]->equals(',')) {
            $tokens->clearAt($trailingComma);
        }

        return true;
    }

    /**
     * @return array{parameters: int, whitespace: list<int>}|null
     */
    private function inspectSimpleParameters(Tokens $tokens, int $openParenthesis, int $closeParenthesis): ?array
    {
        $commas = 0;
        $hasParameter = false;
        $whitespace = [];

        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {

            $token = $tokens[ $index ];

            if ($token->isComment() || ($token->isWhitespace() === false && str_contains($token->getContent(), "\n"))) {
                return null;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {

                $blockEnd = $tokens->findBlockEnd($block[ 'type' ], $index);

                if ($block[ 'type' ] === Tokens::BLOCK_TYPE_ATTRIBUTE
                    || $this->containsLineBreak($tokens, $index, $blockEnd)) {
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

        $lastParameterToken = $tokens->getPrevMeaningfulToken($closeParenthesis);
        $hasTrailingComma = $lastParameterToken !== null && $tokens[ $lastParameterToken ]->equals(',');

        return [
            'parameters' => $hasParameter ? $commas + ($hasTrailingComma ? 0 : 1) : 0,
            'whitespace' => $whitespace,
        ];
    }

    /**
     * @return list<int>
     */
    private function findTopLevelCommas(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $commas = [];

        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {

            $token = $tokens[ $index ];
            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {

                $index = $tokens->findBlockEnd($block[ 'type' ], $index);

                continue;

            }

            if ($token->equals(',')) {
                $commas[] = $index;
            }

        }

        return $commas;
    }

    private function getCompactedParameterLength(Tokens $tokens, int $openParenthesis, int $closeParenthesis): int {
        $parts = [];
        $trailingComma = $tokens->getPrevMeaningfulToken($closeParenthesis);

        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {

            if ($index === $trailingComma && $tokens[ $index ]->equals(',')) {
                continue;
            }

            $token = $tokens[ $index ];

            if ($token->isWhitespace() === false || str_contains($token->getContent(), "\n") === false) {

                $parts[] = $token->getContent();

                continue;

            }

            $previous = $tokens->getPrevMeaningfulToken($index);
            $next = $tokens->getNextMeaningfulToken($index);

            if ($previous !== null
                && $next !== null
                && $previous !== $openParenthesis
                && $next !== $closeParenthesis
                && $tokens[ $next ]->equals(',') === false) {
                $parts[] = ' ';
            }

        }

        return strlen(implode('', $parts));
    }

    private function getLineLengthThrough(Tokens $tokens, int $end): int
    {
        $length = 0;

        for ($index = $end; $index >= 0; $index--) {

            $content = $tokens[ $index ]->getContent();
            $lastLineBreak = strrpos($content, "\n");

            if ($lastLineBreak !== false) {
                return $length + strlen(substr($content, $lastLineBreak + 1));
            }

            $length += strlen($content);

        }

        return $length;
    }

    private function getLineLengthFrom(Tokens $tokens, int $start): int
    {
        $length = 0;

        for ($index = $start, $count = $tokens->count(); $index < $count; $index++) {

            $content = $tokens[ $index ]->getContent();
            $firstLineBreak = strpos($content, "\n");

            if ($firstLineBreak !== false) {
                return $length + $firstLineBreak;
            }

            $length += strlen($content);

            if ($tokens[ $index ]->equalsAny([ '{', ';' ])) {
                return $length;
            }

        }

        return $length;
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
