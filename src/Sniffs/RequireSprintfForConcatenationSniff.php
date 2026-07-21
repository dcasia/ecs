<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class RequireSprintfForConcatenationSniff implements Sniff
{
    /**
     * @var array<string, int>
     */
    private array $reportedStatements = [];

    public function register(): array
    {
        return [ T_STRING_CONCAT, T_CONCAT_EQUAL ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $filename = $phpcsFile->getFilename();

        if ($stackPtr <= ($this->reportedStatements[ $filename ] ?? -1)) {
            return;
        }

        if ($this->isFilesystemPathAssembly($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addError(
            error: 'String concatenation with `.` is not allowed. Use sprintf() with explicit placeholders so the resulting format and dynamic values are clear.',
            stackPtr: $stackPtr,
            code: 'Concatenation',
        );
        $statementEnd = $phpcsFile->findNext(T_SEMICOLON, $stackPtr + 1);
        $this->reportedStatements[ $filename ] = $statementEnd === false ? $stackPtr : $statementEnd;
    }

    private function isFilesystemPathAssembly(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[ $stackPtr ][ 'line' ];

        for ($index = $stackPtr - 1; $index >= 0 && $tokens[ $index ][ 'line' ] === $line; $index--) {

            if (in_array($tokens[ $index ][ 'code' ], [ T_DIR, T_FILE ], true)) {
                return true;
            }

        }

        for ($index = $stackPtr + 1, $count = count($tokens); $index < $count && $tokens[ $index ][ 'line' ] === $line; $index++) {

            if (in_array($tokens[ $index ][ 'code' ], [ T_DIR, T_FILE ], true)) {
                return true;
            }

        }

        return false;
    }
}
