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

final class GuardedCallOrderFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'A non-null guard must precede a multiline call that consumes the guarded variable in a conjunction.',
            codeSamples: [
                new CodeSample("<?php\n\n\$condition = !\$repository->exists(\n    id: \$id,\n) && \$id !== null;\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 1_025;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([ T_BOOLEAN_AND, T_IS_NOT_IDENTICAL ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $candidates = $this->findCandidates($tokens);

        for ($index = count($candidates) - 1; $index >= 0; $index--) {
            $this->moveGuardBeforeCall($tokens, $candidates[ $index ]);
        }
    }

    /**
     * @return list<array{start: int, conjunction: int, end: int, guardStart: int, guardEnd: int}>
     */
    private function findCandidates(Tokens $tokens): array
    {
        $candidates = [];

        for ($index = 1, $count = $tokens->count(); $index < $count; $index++) {

            if (!$tokens[ $index ]->isGivenKind(T_VARIABLE) || !$this->startsStatement($tokens, $index)) {
                continue;
            }

            $assignment = $tokens->getNextMeaningfulToken($index);

            if ($assignment === null || !$tokens[ $assignment ]->equals('=')) {
                continue;
            }

            $end = $this->findStatementEnd($tokens, $assignment);

            if ($end === null) {
                continue;
            }

            $start = $tokens->getNextMeaningfulToken($assignment);
            $conjunction = $start === null ? null : $this->findSingleConjunction($tokens, $start, $end);

            if ($start === null || $conjunction === null) {

                $index = $end;

                continue;

            }

            $guard = $this->findNullGuard($tokens, $conjunction, $end);

            $condition = $guard === null
                || !$this->containsGuardedMultilineCall(
                    tokens: $tokens,
                    start: $start,
                    end: $conjunction - 1,
                    variable: $guard[ 'variable' ],
                );

            if ($condition) {

                $index = $end;

                continue;

            }

            $candidates[] = [
                'start' => $start,
                'conjunction' => $conjunction,
                'end' => $end,
                'guardStart' => $guard[ 'start' ],
                'guardEnd' => $guard[ 'end' ],
            ];

            $index = $end;

        }

        return $candidates;
    }

    private function startsStatement(Tokens $tokens, int $variable): bool
    {
        $previous = $tokens->getPrevMeaningfulToken($variable);

        while ($previous !== null && $tokens[ $previous ]->isComment()) {
            $previous = $tokens->getPrevMeaningfulToken($previous);
        }

        if ($previous === null) {
            return false;
        }

        return $tokens[ $previous ]->equalsAny([ ';', '{', '}', ':' ])
            || $tokens[ $previous ]->isGivenKind(T_OPEN_TAG);
    }

    private function findStatementEnd(Tokens $tokens, int $assignment): ?int
    {
        for ($index = $assignment + 1, $count = $tokens->count(); $index < $count; $index++) {

            $token = $tokens[ $index ];

            if ($token->equals(';')) {
                return $index;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {
                $index = $tokens->findBlockEnd($block[ 'type' ], $index);
            }

        }

        return null;
    }

    private function findSingleConjunction(Tokens $tokens, int $start, int $end): ?int
    {
        $conjunction = null;

        for ($index = $start; $index < $end; $index++) {

            $token = $tokens[ $index ];

            if ($token->isComment() || $token->isGivenKind(T_BOOLEAN_OR)) {
                return null;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {

                $index = $tokens->findBlockEnd($block[ 'type' ], $index);

                continue;

            }

            if (!$token->isGivenKind(T_BOOLEAN_AND)) {
                continue;
            }

            if ($conjunction !== null) {
                return null;
            }

            $conjunction = $index;

        }

        return $conjunction;
    }

    /**
     * @return array{start: int, end: int, variable: string}|null
     */
    private function findNullGuard(Tokens $tokens, int $conjunction, int $end): ?array
    {
        $meaningful = [];

        for ($index = $conjunction + 1; $index < $end; $index++) {

            if ($tokens[ $index ]->isComment()) {
                return null;
            }

            if (!$tokens[ $index ]->isWhitespace()) {
                $meaningful[] = $index;
            }

        }

        if (count($meaningful) !== 3 || !$tokens[ $meaningful[ 1 ] ]->isGivenKind(T_IS_NOT_IDENTICAL)) {
            return null;
        }

        $variable = null;

        if ($tokens[ $meaningful[ 0 ] ]->isGivenKind(T_VARIABLE) && $this->isNull($tokens[ $meaningful[ 2 ] ])) {

            $variable = $tokens[ $meaningful[ 0 ] ]->getContent();

        } else if ($this->isNull($tokens[ $meaningful[ 0 ] ]) && $tokens[ $meaningful[ 2 ] ]->isGivenKind(T_VARIABLE)) {

            $variable = $tokens[ $meaningful[ 2 ] ]->getContent();

        }

        if ($variable === null) {
            return null;
        }

        return [
            'start' => $meaningful[ 0 ],
            'end' => $meaningful[ 2 ],
            'variable' => $variable,
        ];
    }

    private function isNull(Token $token): bool
    {
        return $token->isGivenKind(T_STRING) && strtolower($token->getContent()) === 'null';
    }

    private function containsGuardedMultilineCall(Tokens $tokens, int $start, int $end, string $variable): bool
    {
        for ($index = $start; $index <= $end; $index++) {

            if (!$tokens[ $index ]->equals('(')) {
                continue;
            }

            $callable = $tokens->getPrevMeaningfulToken($index);

            if ($callable === null || !$this->isCallableToken($tokens[ $callable ])) {
                continue;
            }

            $close = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);

            if (
                $close <= $end
                && $this->containsLineBreak($tokens, $index, $close)
                && $this->containsVariable($tokens, $index, $close, $variable)
            ) {
                return true;
            }

            $index = $close;

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

    private function containsVariable(Tokens $tokens, int $start, int $end, string $variable): bool
    {
        for ($index = $start; $index <= $end; $index++) {

            if ($tokens[ $index ]->isGivenKind(T_VARIABLE) && $tokens[ $index ]->getContent() === $variable) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array{start: int, conjunction: int, end: int, guardStart: int, guardEnd: int} $candidate
     */
    private function moveGuardBeforeCall(Tokens $tokens, array $candidate): void
    {
        $call = [];
        $guard = [];

        for ($index = $candidate[ 'start' ]; $index < $candidate[ 'conjunction' ]; $index++) {
            $call[] = $tokens[ $index ];
        }

        for ($index = $candidate[ 'guardStart' ]; $index <= $candidate[ 'guardEnd' ]; $index++) {
            $guard[] = $tokens[ $index ];
        }

        while ($call !== [] && end($call)->isWhitespace()) {
            array_pop($call);
        }

        $tokens->clearRange($candidate[ 'start' ], $candidate[ 'end' ] - 1);
        $tokens->insertAt(
            index: $candidate[ 'start' ],
            items: [
                ...$guard,
                new Token([ T_WHITESPACE, ' ' ]),
                new Token([ T_BOOLEAN_AND, '&&' ]),
                new Token([ T_WHITESPACE, ' ' ]),
                ...$call,
            ],
        );
    }
}
