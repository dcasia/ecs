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

final class FunctionOpeningBracketFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Named function and method opening brackets must be placed on the line after the signature.',
            codeSamples: [
                new CodeSample("<?php\n\nfinal class Service\n{\n    public function __construct(\n        private readonly Repository \$repository,\n    ) {\n    }\n}\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 950;
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
            $openBracket = $this->findOpeningBracket($tokens, $closeParenthesis);

            if ($openBracket === null) {
                continue;
            }

            $indent = $this->findIndent($tokens, $index);
            $whitespace = $openBracket - 1;
            $content = $this->whitespacesConfig->getLineEnding() . $indent;

            if ($tokens[ $whitespace ]->isWhitespace()) {

                $tokens[ $whitespace ] = new Token([ T_WHITESPACE, $content ]);

                continue;

            }

            $tokens->insertAt($openBracket, new Token([ T_WHITESPACE, $content ]));

        }
    }

    private function findIndent(Tokens $tokens, int $function): string
    {
        for ($index = $function - 1; $index >= 0; $index--) {

            $content = $tokens[ $index ]->getContent();
            $lineBreak = strrpos($content, "\n");

            if ($lineBreak !== false) {
                return substr($content, $lineBreak + 1);
            }

        }

        return '';
    }

    private function findOpeningBracket(Tokens $tokens, int $closeParenthesis): ?int
    {
        for ($index = $closeParenthesis + 1, $count = $tokens->count(); $index < $count; $index++) {

            if ($tokens[ $index ]->equals(';')) {
                return null;
            }

            if ($tokens[ $index ]->equals('{')) {
                return $index;
            }

        }

        return null;
    }
}
