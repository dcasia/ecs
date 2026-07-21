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

final class LaravelEmptyToBlankFixer extends AbstractFixer
{
    private readonly bool $laravelInstalled;

    public function __construct()
    {
        parent::__construct();

        $this->laravelInstalled = class_exists('Illuminate\Foundation\Application');
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Laravel applications should use blank() and filled() instead of empty() and !empty().',
            codeSamples: [
                new CodeSample("<?php\n\nempty(\$lines);\n!empty(\$records);\n"),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->laravelInstalled && $tokens->isTokenKindFound(T_EMPTY);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; $index--) {

            if (!$tokens[ $index ]->isGivenKind(T_EMPTY)) {
                continue;
            }

            $previous = $tokens->getPrevMeaningfulToken($index);

            if ($previous !== null && $tokens[ $previous ]->equals('!')) {

                $tokens->clearAt($previous);

                for ($whitespace = $previous + 1; $whitespace < $index; $whitespace++) {

                    if ($tokens[ $whitespace ]->isWhitespace()) {
                        $tokens->clearAt($whitespace);
                    }

                }

                $tokens[ $index ] = new Token([ T_STRING, 'filled' ]);

                continue;

            }

            $tokens[ $index ] = new Token([ T_STRING, 'blank' ]);

        }
    }
}
