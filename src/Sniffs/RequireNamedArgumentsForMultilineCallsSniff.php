<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class RequireNamedArgumentsForMultilineCallsSniff implements Sniff
{
    public function register(): array
    {
        $tokens = Tokens::NAME_TOKENS;
        $tokens[] = T_SELF;
        $tokens[] = T_STATIC;
        $tokens[] = T_PARENT;
        $tokens[] = T_VARIABLE;
        $tokens[] = T_CLOSE_CURLY_BRACKET;
        $tokens[] = T_CLOSE_PARENTHESIS;

        return $tokens;
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $previous = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, true);

        if ($previous !== false && in_array($tokens[ $previous ][ 'code' ], [ T_FUNCTION, T_CLASS ], true)) {
            return;
        }

        if ($tokens[ $stackPtr ][ 'code' ] === T_CLOSE_CURLY_BRACKET && isset($tokens[ $stackPtr ][ 'scope_condition' ])) {
            return;
        }

        $openParenthesis = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, true);

        if (
            $openParenthesis === false
            || $tokens[ $openParenthesis ][ 'code' ] !== T_OPEN_PARENTHESIS
            || !isset($tokens[ $openParenthesis ][ 'parenthesis_closer' ])
        ) {
            return;
        }

        $closeParenthesis = $tokens[ $openParenthesis ][ 'parenthesis_closer' ];

        if ($tokens[ $openParenthesis ][ 'line' ] === $tokens[ $closeParenthesis ][ 'line' ]) {
            return;
        }

        $arguments = [];
        $separator = $openParenthesis;

        while (true) {

            $argument = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $separator + 1, $closeParenthesis, true);

            if ($argument === false) {
                break;
            }

            $arguments[] = $argument;
            $separator = $this->findNextArgumentSeparator($phpcsFile, $argument, $closeParenthesis);

            if ($separator === false) {
                break;
            }

        }

        if (
            count($arguments) < 2
            || $tokens[ $arguments[ 0 ] ][ 'line' ] === $tokens[ $openParenthesis ][ 'line' ]
        ) {
            return;
        }

        foreach ($arguments as $argumentNumber => $argument) {

            if (!in_array($tokens[ $argument ][ 'code' ], [ T_PARAM_NAME, T_ELLIPSIS ], true)) {

                $phpcsFile->addError(
                    error: 'Argument %d of a multiline call must be named. Verify the callable signature and add its declared parameter name, for example `value:` or `callback:`.',
                    stackPtr: $argument,
                    code: 'Missing',
                    data: [ $argumentNumber + 1 ],
                );

            }

        }
    }

    private function findNextArgumentSeparator(File $phpcsFile, int $start, int $closeParenthesis): false | int
    {
        $tokens = $phpcsFile->getTokens();
        $searchTokens = [ T_COMMA, T_CLOSURE, T_FN, T_ANON_CLASS, T_OPEN_SHORT_ARRAY, T_MATCH ];

        if (in_array($tokens[ $start ][ 'code' ], [ T_CLOSURE, T_ANON_CLASS, T_MATCH ], true)) {

            $start = $tokens[ $start ][ 'scope_closer' ];

        } else if ($tokens[ $start ][ 'code' ] === T_FN) {

            $start = $tokens[ $start ][ 'scope_closer' ] - 1;

        } else if ($tokens[ $start ][ 'code' ] === T_OPEN_SHORT_ARRAY) {

            $start = $tokens[ $start ][ 'bracket_closer' ];

        }

        $separator = $start;

        while (($separator = $phpcsFile->findNext($searchTokens, $separator + 1, $closeParenthesis)) !== false) {

            if (in_array($tokens[ $separator ][ 'code' ], [ T_CLOSURE, T_ANON_CLASS, T_MATCH ], true)) {

                $separator = $tokens[ $separator ][ 'scope_closer' ];

                continue;

            }

            if ($tokens[ $separator ][ 'code' ] === T_FN) {

                $separator = $tokens[ $separator ][ 'scope_closer' ] - 1;

                continue;

            }

            if ($tokens[ $separator ][ 'code' ] === T_OPEN_SHORT_ARRAY) {

                $separator = $tokens[ $separator ][ 'bracket_closer' ];

                continue;

            }

            $parentheses = $tokens[ $separator ][ 'nested_parenthesis' ];

            if (array_pop($parentheses) === $closeParenthesis) {
                return $separator;
            }

        }

        return false;
    }
}
