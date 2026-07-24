<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\IndentationTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class StatementGroupingFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    use IndentationTrait;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Adjacent expression statements must be grouped by their variable receiver or call type.',
            [],
        );
    }

    public function getPriority(): int
    {
        return -110;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(';');
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $statements = $this->findGroupableStatements($tokens);

        for ($index = count($statements) - 1; $index > 0; $index--) {

            $previous = $statements[ $index - 1 ];
            $current = $statements[ $index ];

            if ($this->containsOnlyWhitespace($tokens, $previous[ 'end' ], $current[ 'start' ]) === false) {
                continue;
            }

            $requiresBlankLine = $this->belongToSameGroup($previous, $current) === false
                || $previous[ 'multiline' ];

            $this->normalizeBoundaryWhitespace(
                $tokens,
                $previous[ 'end' ],
                $current[ 'start' ],
                $requiresBlankLine,
            );

        }
    }

    /**
     * @return list<array{start: int, end: int, kind: string, receiver: ?string, multiline: bool}>
     */
    private function findGroupableStatements(Tokens $tokens): array
    {
        $statements = [];
        $nestedExpressionDepth = 0;

        foreach ($tokens as $index => $token) {

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $this->isExpressionBlock($block[ 'type' ])) {

                $nestedExpressionDepth += $block[ 'isStart' ] ? 1 : -1;

                continue;

            }

            if ($nestedExpressionDepth > 0 || $token->equals(';') === false) {
                continue;
            }

            $start = $this->findStatementStart($tokens, $index);

            if ($start === null) {
                continue;
            }

            $group = $this->findGroup($tokens, $start, $index);

            if ($group === null) {
                continue;
            }

            $statements[] = [
                'start' => $start,
                'end' => $index,
                'kind' => $group[ 'kind' ],
                'receiver' => $group[ 'receiver' ],
                'multiline' => $tokens->isPartialCodeMultiline($start, $index),
            ];

        }

        return $statements;
    }

    private function isExpressionBlock(int $blockType): bool
    {
        return !in_array($blockType, [
            Tokens::BLOCK_TYPE_BRACE,
            Tokens::BLOCK_TYPE_PROPERTY_HOOK,
        ], true);
    }

    private function findStatementStart(Tokens $tokens, int $end): ?int
    {
        $cursor = $end;

        while (($previous = $tokens->getPrevMeaningfulToken($cursor)) !== null) {

            $token = $tokens[ $previous ];

            if ($token->equalsAny([ ';', '{' ]) || $token->isGivenKind(T_OPEN_TAG)) {
                return $tokens->getNextMeaningfulToken($previous);
            }

            $block = Tokens::detectBlockType($token);

            if ($block === null || $block[ 'isStart' ]) {

                $cursor = $previous;

                continue;

            }

            if ($token->equals('}')) {

                $next = $tokens->getNextMeaningfulToken($previous);

                if ($next === null || $this->continuesExpression($tokens[ $next ]) === false) {
                    return $next;
                }

            }

            $cursor = $tokens->findBlockStart($block[ 'type' ], $previous);

        }

        return null;
    }

    private function continuesExpression(Token $token): bool
    {
        return $token->equalsAny([ ';', ',', '(', ')', ']' ])
            || $token->isGivenKind([ T_DOUBLE_COLON, ...Token::getObjectOperatorKinds() ]);
    }

    /**
     * @return array{kind: string, receiver: ?string}|null
     */
    private function findGroup(Tokens $tokens, int $start, int $end): ?array
    {
        $token = $tokens[ $start ];
        $receiver = $token->isGivenKind(T_VARIABLE) ? $token->getContent() : null;

        if ($this->isAssignment($tokens, $start, $end)) {

            return [
                'kind' => 'assignment',
                'receiver' => $receiver,
            ];

        }

        if ($receiver !== null) {

            return [
                'kind' => 'variable-receiver',
                'receiver' => $receiver,
            ];

        }

        $next = $tokens->getNextMeaningfulToken($start);

        if ($next === null) {
            return null;
        }

        if ($tokens[ $next ]->isGivenKind(T_DOUBLE_COLON)) {

            return [
                'kind' => 'static-call',
                'receiver' => null,
            ];

        }

        if ($tokens[ $next ]->equals('(')) {

            return [
                'kind' => 'function-call',
                'receiver' => null,
            ];

        }

        return null;
    }

    private function isAssignment(Tokens $tokens, int $start, int $end): bool
    {
        $blockDepth = 0;

        for ($index = $start; $index < $end; $index++) {

            $token = $tokens[ $index ];
            $block = Tokens::detectBlockType($token);

            if ($block !== null) {

                $blockDepth += $block[ 'isStart' ] ? 1 : -1;

                continue;

            }

            if ($blockDepth === 0 && $token->equals('=')) {
                return true;
            }

            if ($blockDepth === 0 && $token->isGivenKind([
                T_AND_EQUAL,
                T_COALESCE_EQUAL,
                T_CONCAT_EQUAL,
                T_DIV_EQUAL,
                T_MINUS_EQUAL,
                T_MOD_EQUAL,
                T_MUL_EQUAL,
                T_OR_EQUAL,
                T_PLUS_EQUAL,
                T_POW_EQUAL,
                T_SL_EQUAL,
                T_SR_EQUAL,
                T_XOR_EQUAL,
            ])) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array{kind: string, receiver: ?string} $previous
     * @param array{kind: string, receiver: ?string} $current
     */
    private function belongToSameGroup(array $previous, array $current): bool
    {
        if ($previous[ 'kind' ] === 'assignment' && $current[ 'kind' ] === 'assignment') {
            return true;
        }

        if ($previous[ 'receiver' ] !== null
            && $previous[ 'receiver' ] === $current[ 'receiver' ]) {
            return true;
        }

        return $previous[ 'kind' ] === $current[ 'kind' ]
            && in_array($previous[ 'kind' ], [ 'static-call', 'function-call' ], true);
    }

    private function containsOnlyWhitespace(Tokens $tokens, int $previousEnd, int $currentStart): bool
    {
        for ($index = $previousEnd + 1; $index < $currentStart; $index++) {

            if ($tokens[ $index ]->isWhitespace() === false && $tokens->isEmptyAt($index) === false) {
                return false;
            }

        }

        return true;
    }

    private function normalizeBoundaryWhitespace(
        Tokens $tokens,
        int $previousEnd,
        int $currentStart,
        bool $blankLine,
    ): void {
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        $lineBreaks = $blankLine ? 2 : 1;
        $whitespaceContent = sprintf(
            '%s%s',
            str_repeat($lineEnding, $lineBreaks),
            $this->getLineIndentation($tokens, $currentStart),
        );

        $whitespaceIndex = null;

        for ($index = $previousEnd + 1; $index < $currentStart; $index++) {

            if ($tokens[ $index ]->isWhitespace() === false) {
                continue;
            }

            if ($whitespaceIndex === null) {

                $whitespaceIndex = $index;

                continue;

            }

            $tokens->clearAt($index);

        }

        if ($whitespaceIndex === null) {

            $tokens->insertAt(
                $previousEnd + 1,
                new Token([ T_WHITESPACE, $whitespaceContent ]),
            );

            return;

        }

        $tokens[ $whitespaceIndex ] = new Token([ T_WHITESPACE, $whitespaceContent ]);
    }
}
