<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class RequireParameterTypeSniff implements Sniff
{
    public function register(): array
    {
        return [ T_FUNCTION, T_CLOSURE, T_FN ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        foreach ($phpcsFile->getMethodParameters($stackPtr) as $parameter) {

            if ($parameter[ 'type_hint' ] !== '') {
                continue;
            }

            $phpcsFile->addError(
                error: 'Parameter %s must have a native type declaration. Prefer a precise type or explicit union; do not use mixed unless the accepted types are genuinely too numerous to list meaningfully.',
                stackPtr: $parameter[ 'token' ],
                code: 'Missing',
                data: [ $parameter[ 'name' ] ],
            );

        }
    }
}
