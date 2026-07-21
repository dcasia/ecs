<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class RequireShallowMethodChainsSniff implements Sniff
{
    private const int MAXIMUM_NESTING_LEVEL = 3;

    public function register(): array
    {
        return [ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        if (!$this->isFirstChainOperator($phpcsFile, $stackPtr) || $this->countChainOperators($phpcsFile, $stackPtr) < 2) {
            return;
        }

        [ $nestingLevel, $containingCall ] = $this->findNestingLevel($phpcsFile, $stackPtr);

        if ($nestingLevel <= self::MAXIMUM_NESTING_LEVEL) {
            return;
        }

        $phpcsFile->addError(
            error: 'Nested method chain reaches level %d; the maximum is %d. Extract the chain passed to `%s()` into a descriptive private method on the class and pass its result as `$this->...()`; do not add local variables merely to reduce nesting.',
            stackPtr: $stackPtr,
            code: 'TooDeep',
            data: [ $nestingLevel, self::MAXIMUM_NESTING_LEVEL, $containingCall ],
        );
    }

    private function isFirstChainOperator(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $receiver = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, true);

        if ($receiver === false || $tokens[ $receiver ][ 'code' ] !== T_CLOSE_PARENTHESIS) {
            return true;
        }

        $openParenthesis = $tokens[ $receiver ][ 'parenthesis_opener' ] ?? null;

        if (!is_int($openParenthesis)) {
            return true;
        }

        $method = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $openParenthesis - 1, null, true);

        if ($method === false) {
            return true;
        }

        $previous = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $method - 1, null, true);

        return $previous === false || !$this->isObjectOperator($tokens[ $previous ][ 'code' ]);
    }

    private function countChainOperators(File $phpcsFile, int $stackPtr): int
    {
        $tokens = $phpcsFile->getTokens();
        $operator = $stackPtr;
        $count = 0;

        while (true) {

            $count++;
            $method = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $operator + 1, null, true);

            if ($method === false) {
                return $count;
            }

            $openParenthesis = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $method + 1, null, true);

            if (
                $openParenthesis === false
                || $tokens[ $openParenthesis ][ 'code' ] !== T_OPEN_PARENTHESIS
                || !isset($tokens[ $openParenthesis ][ 'parenthesis_closer' ])
            ) {
                return $count;
            }

            $operator = $phpcsFile->findNext(
                types: Tokens::EMPTY_TOKENS,
                start: $tokens[ $openParenthesis ][ 'parenthesis_closer' ] + 1,
                end: null,
                exclude: true,
            );

            if ($operator === false || !$this->isObjectOperator($tokens[ $operator ][ 'code' ])) {
                return $count;
            }

        }
    }

    /**
     * @return array{int, string}
     */
    private function findNestingLevel(File $phpcsFile, int $stackPtr): array
    {
        $tokens = $phpcsFile->getTokens();
        $nestingLevel = 1;
        $containingCall = 'containing method';

        foreach (array_keys($tokens[ $stackPtr ][ 'nested_parenthesis' ] ?? []) as $openParenthesis) {

            $method = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $openParenthesis - 1, null, true);

            if ($method === false || $tokens[ $method ][ 'code' ] !== T_STRING) {
                continue;
            }

            $operator = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $method - 1, null, true);

            if ($operator === false || !$this->isObjectOperator($tokens[ $operator ][ 'code' ])) {
                continue;
            }

            $nestingLevel++;
            $containingCall = $tokens[ $method ][ 'content' ];

        }

        return [ $nestingLevel, $containingCall ];
    }

    private function isObjectOperator(int | string $code): bool
    {
        return in_array($code, [ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ], true);
    }
}
