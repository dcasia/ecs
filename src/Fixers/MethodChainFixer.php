<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class MethodChainFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    private const int MAXIMUM_LINE_LENGTH = 120;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Method chains with multiline call arguments must place every chained call on its own line.',
            codeSamples: [
                new CodeSample("<?php\n\nUserFactory::new()->create([\n    'name' => 'Test User',\n    'email' => 'test@example.com',\n])->assignRole('Admin');\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 1_100;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $operatorsToBreak = [];
        $operatorsToJoin = [];

        foreach ($this->findChains($tokens) as $chain) {

            if (!$this->mustBreakChain($tokens, $chain)) {

                if ($this->isExpandedStaticConstructorChain($tokens, $chain)) {

                    $operatorsToBreak[ $chain[ 'operators' ][ 0 ] ] = true;

                    continue;

                }

                if ($this->mustPreserveExpandedChain($tokens, $chain)) {
                    continue;
                }

                $operators = $this->hasMultilineFinalCall($tokens, $chain)
                    ? $chain[ 'operators' ]
                    : [ $chain[ 'operators' ][ 0 ] ];

                foreach ($operators as $operator) {
                    $operatorsToJoin[ $operator ] = true;
                }

                continue;

            }

            $indentation = $this->findStatementIndentation($tokens, $chain[ 'operators' ][ 0 ]);

            if ($indentation !== null) {

                foreach ($chain[ 'calls' ] as $call) {
                    $this->compactShortArrayArgument($tokens, $call, $indentation);
                }

            }

            foreach ($chain[ 'operators' ] as $operator) {
                $operatorsToBreak[ $operator ] = true;
            }

        }

        foreach (array_keys($operatorsToJoin) as $operator) {
            $this->joinBeforeOperator($tokens, $operator);
        }

        $operators = array_keys($operatorsToBreak);
        rsort($operators);

        foreach ($operators as $operator) {
            $this->breakBeforeOperator($tokens, $operator);
        }
    }

    /**
     * @return list<array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * }>
     */
    private function findChains(Tokens $tokens): array
    {
        $chains = [];
        $seen = [];

        for ($index = 0, $count = $tokens->count(); $index < $count; $index++) {

            if (!$this->isObjectOperator($tokens[ $index ])) {
                continue;
            }

            $operators = $this->collectChainOperators($tokens, $index);

            if ($operators === []) {
                continue;
            }

            $firstOperator = $operators[ 0 ];

            if (isset($seen[ $firstOperator ])) {
                continue;
            }

            $calls = [];

            foreach ($operators as $operator) {

                $call = $this->findCall($tokens, $operator);

                if ($call === null) {
                    continue 2;
                }

                $calls[] = [
                    'operator' => $operator,
                    'open' => $call[ 'open' ],
                    'close' => $call[ 'close' ],
                ];

            }

            $seen[ $firstOperator ] = true;

            $chains[] = [
                'operators' => $operators,
                'calls' => $calls,
            ];

        }

        return $chains;
    }

    /**
     * @return list<int>
     */
    private function collectChainOperators(Tokens $tokens, int $operator): array
    {
        $head = $operator;

        while (true) {

            $receiverClose = $tokens->getPrevMeaningfulToken($head);

            if ($receiverClose === null || !$tokens[ $receiverClose ]->equals(')')) {
                break;
            }

            $receiverOpen = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $receiverClose);
            $method = $tokens->getPrevMeaningfulToken($receiverOpen);

            if ($method === null) {
                break;
            }

            $previousOperator = $tokens->getPrevMeaningfulToken($method);

            if ($previousOperator === null || !$this->isObjectOperator($tokens[ $previousOperator ])) {
                break;
            }

            $head = $previousOperator;

        }

        $operators = [];
        $current = $head;

        while (true) {

            $call = $this->findCall($tokens, $current);

            if ($call === null) {
                return [];
            }

            $operators[] = $current;
            $nextOperator = $tokens->getNextMeaningfulToken($call[ 'close' ]);

            if ($nextOperator === null || !$this->isObjectOperator($tokens[ $nextOperator ])) {
                break;
            }

            $current = $nextOperator;

        }

        return $operators;
    }

    /**
     * @return array{open: int, close: int}|null
     */
    private function findCall(Tokens $tokens, int $operator): ?array
    {
        $method = $tokens->getNextMeaningfulToken($operator);

        if ($method === null || !$tokens[ $method ]->isGivenKind(T_STRING)) {
            return null;
        }

        $open = $tokens->getNextMeaningfulToken($method);

        if ($open === null || !$tokens[ $open ]->equals('(')) {
            return null;
        }

        return [
            'open' => $open,
            'close' => $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $open),
        ];
    }

    /**
     * @param array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * } $chain
     */
    private function mustBreakChain(Tokens $tokens, array $chain): bool
    {
        $firstOperator = $chain[ 'operators' ][ 0 ];
        $receiverClose = $tokens->getPrevMeaningfulToken($firstOperator);

        if ($receiverClose !== null && $tokens[ $receiverClose ]->equals(')')) {

            $receiverOpen = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $receiverClose);

            if ($this->containsLineBreak($tokens, $receiverOpen, $receiverClose)) {
                return true;
            }

        }

        $lastCall = count($chain[ 'calls' ]) - 1;

        for ($index = 0; $index < $lastCall; $index++) {

            $call = $chain[ 'calls' ][ $index ];

            if ($this->containsLineBreak($tokens, $call[ 'open' ], $call[ 'close' ])) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * } $chain
     */
    private function isExpandedStaticConstructorChain(Tokens $tokens, array $chain): bool
    {
        if (!$this->hasExpandedOperator($tokens, $chain)) {
            return false;
        }

        $firstOperator = $chain[ 'operators' ][ 0 ];
        $receiverClose = $tokens->getPrevMeaningfulToken($firstOperator);

        if ($receiverClose === null || !$tokens[ $receiverClose ]->equals(')')) {
            return false;
        }

        $receiverOpen = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $receiverClose);
        $method = $tokens->getPrevMeaningfulToken($receiverOpen);

        if ($method === null) {
            return false;
        }

        $staticOperator = $tokens->getPrevMeaningfulToken($method);

        return $staticOperator !== null && $tokens[ $staticOperator ]->isGivenKind(T_DOUBLE_COLON);
    }

    /**
     * @param array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * } $chain
     */
    private function hasExpandedOperator(Tokens $tokens, array $chain): bool
    {
        foreach ($chain[ 'operators' ] as $operator) {

            $whitespace = $operator - 1;

            if (
                $whitespace >= 0
                && $tokens[ $whitespace ]->isWhitespace()
                && str_contains($tokens[ $whitespace ]->getContent(), "\n")
            ) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * } $chain
     */
    private function mustPreserveExpandedChain(Tokens $tokens, array $chain): bool
    {
        if (!$this->hasExpandedOperator($tokens, $chain)) {
            return false;
        }

        $whitespaceBeforeOperators = [];

        foreach ($chain[ 'operators' ] as $operator) {
            $whitespaceBeforeOperators[ $operator - 1 ] = true;
        }

        $firstOperator = $chain[ 'operators' ][ 0 ];
        $beforeFirstOperator = $firstOperator - 1;
        $content = '';

        for ($index = $beforeFirstOperator; $index >= 0; $index--) {

            $tokenContent = $tokens[ $index ]->getContent();

            if (
                $index === $beforeFirstOperator
                && isset($whitespaceBeforeOperators[ $index ])
                && str_contains($tokenContent, "\n")
            ) {
                continue;
            }

            $lineFeed = strrpos($tokenContent, "\n");
            $carriageReturn = strrpos($tokenContent, "\r");
            $lineBreak = max($lineFeed === false ? -1 : $lineFeed, $carriageReturn === false ? -1 : $carriageReturn);

            if ($lineBreak >= 0) {

                $content = sprintf('%s%s', substr($tokenContent, $lineBreak + 1), $content);

                break;

            }

            $content = sprintf('%s%s', $tokenContent, $content);

        }

        $lastCall = $chain[ 'calls' ][ count($chain[ 'calls' ]) - 1 ];

        for ($index = $firstOperator; $index <= $lastCall[ 'close' ]; $index++) {

            if (
                isset($whitespaceBeforeOperators[ $index ])
                && $tokens[ $index ]->isWhitespace()
                && str_contains($tokens[ $index ]->getContent(), "\n")
            ) {
                continue;
            }

            $content = sprintf('%s%s', $content, $tokens[ $index ]->getContent());

        }

        $lines = preg_split('/\R/', $content);

        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {

            if (strlen($line) > self::MAXIMUM_LINE_LENGTH) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array{
     *     operators: list<int>,
     *     calls: list<array{operator: int, open: int, close: int}>
     * } $chain
     */
    private function hasMultilineFinalCall(Tokens $tokens, array $chain): bool
    {
        $lastCall = $chain[ 'calls' ][ count($chain[ 'calls' ]) - 1 ];

        return $this->containsLineBreak($tokens, $lastCall[ 'open' ], $lastCall[ 'close' ]);
    }

    /**
     * @param array{operator: int, open: int, close: int} $call
     */
    private function compactShortArrayArgument(Tokens $tokens, array $call, int $indentation): void
    {
        $arrayOpen = $tokens->getNextMeaningfulToken($call[ 'open' ]);

        if ($arrayOpen === null || !$tokens[ $arrayOpen ]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            return;
        }

        $arrayClose = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $arrayOpen);

        if ($tokens->getNextMeaningfulToken($arrayClose) !== $call[ 'close' ]) {
            return;
        }

        $whitespace = [];

        for ($index = $arrayOpen + 1; $index < $arrayClose; $index++) {

            $token = $tokens[ $index ];

            if ($token->isComment()) {
                return;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {
                return;
            }

            if ($token->isWhitespace() && str_contains($token->getContent(), "\n")) {
                $whitespace[] = $index;
            }

        }

        if ($whitespace === []) {
            return;
        }

        $compactWhitespace = array_fill_keys($whitespace, true);
        $callContent = '';

        for ($index = $call[ 'operator' ]; $index <= $call[ 'close' ]; $index++) {

            if (isset($compactWhitespace[ $index ])) {

                $callContent = sprintf('%s ', $callContent);

                continue;

            }

            $content = $tokens[ $index ]->getContent();

            if (str_contains($content, "\n")) {
                return;
            }

            $callContent = sprintf('%s%s', $callContent, $content);

        }

        if ($indentation + 4 + strlen($callContent) > self::MAXIMUM_LINE_LENGTH) {
            return;
        }

        foreach ($whitespace as $index) {
            $tokens[ $index ] = new Token([ T_WHITESPACE, ' ' ]);
        }
    }

    private function findStatementIndentation(Tokens $tokens, int $operator): ?int
    {
        for ($index = $operator - 1; $index >= 0; $index--) {

            $token = $tokens[ $index ];

            if ($token->equalsAny([ ';', '{', '}' ])) {

                $start = $tokens->getNextNonWhitespace($index);

                if ($start === null || $start <= $index) {
                    return null;
                }

                $whitespace = $tokens[ $start - 1 ]->getContent();
                $lineBreak = max((int) strrpos($whitespace, "\n"), (int) strrpos($whitespace, "\r"));

                return strlen(substr($whitespace, $lineBreak + 1));

            }

            $block = Tokens::detectBlockType($token);

            if ($block === null) {
                continue;
            }

            if ($block[ 'isStart' ]) {
                return null;
            }

            $index = $tokens->findBlockStart($block[ 'type' ], $index);

        }

        return null;
    }

    private function joinBeforeOperator(Tokens $tokens, int $operator): void
    {
        $before = $operator - 1;

        if (
            $before >= 0
            && $tokens[ $before ]->isWhitespace()
            && str_contains($tokens[ $before ]->getContent(), "\n")
        ) {
            $tokens->clearAt($before);
        }
    }

    private function breakBeforeOperator(Tokens $tokens, int $operator): void
    {
        $before = $operator - 1;

        if ($before >= 0 && $tokens[ $before ]->isWhitespace()) {

            if (!str_contains($tokens[ $before ]->getContent(), "\n")) {
                $tokens[ $before ] = new Token([ T_WHITESPACE, $this->whitespacesConfig->getLineEnding() ]);
            }

            return;

        }

        $tokens->insertAt(
            index: $operator,
            items: [ new Token([ T_WHITESPACE, $this->whitespacesConfig->getLineEnding() ]) ],
        );
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

    private function isObjectOperator(Token $token): bool
    {
        return $token->isGivenKind([ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ]);
    }
}
