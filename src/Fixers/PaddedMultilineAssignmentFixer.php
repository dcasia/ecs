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

final class PaddedMultilineAssignmentFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Multiline variable assignments must be surrounded by blank lines.',
            codeSamples: [
                new CodeSample("<?php\n\n\$assigned = LeadFactory::new()->create([\n    'name' => 'Assigned',\n]);\n\$shared = LeadFactory::new()->create([\n    'name' => 'Shared',\n]);\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return 900;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('=');
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $assignments = $this->findMultilineAssignments($tokens);

        for ($index = count($assignments) - 1; $index >= 0; $index--) {

            $assignment = $assignments[ $index ];

            $this->ensureBlankLineAfter($tokens, $assignment[ 'end' ]);
            $this->ensureBlankLineBefore($tokens, $assignment[ 'start' ]);

        }
    }

    /**
     * @return list<array{start: int, end: int}>
     */
    private function findMultilineAssignments(Tokens $tokens): array
    {
        $assignments = [];
        $parenthesisDepth = 0;

        for ($index = 1, $count = $tokens->count(); $index < $count; $index++) {

            $block = Tokens::detectBlockType($tokens[ $index ]);

            if (
                $block !== null
                && in_array($block[ 'type' ], [
                    Tokens::BLOCK_TYPE_CLASS_INSTANTIATION_PARENTHESIS,
                    Tokens::BLOCK_TYPE_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS,
                    Tokens::BLOCK_TYPE_PARENTHESIS,
                ], true)
            ) {

                $parenthesisDepth += $block[ 'isStart' ] ? 1 : -1;

                continue;

            }

            if ($parenthesisDepth > 0) {
                continue;
            }

            if (!$tokens[ $index ]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            $start = $this->findStartIncludingComments($tokens, $index);
            $previous = $tokens->getPrevNonWhitespace($start);

            if ($previous === null || !$tokens[ $previous ]->equalsAny([ ';', '{', '}', ':' ])) {
                continue;
            }

            $end = $this->findAssignmentEnd($tokens, $index);

            if ($end === null || !$this->containsLineBreak($tokens, $index, $end)) {
                continue;
            }

            $assignments[] = [
                'start' => $start,
                'end' => $this->findEndIncludingTrailingComment($tokens, $end),
            ];

            $index = $end;

        }

        return $assignments;
    }

    private function findAssignmentEnd(Tokens $tokens, int $start): ?int
    {
        $hasAssignment = false;

        for ($index = $start, $count = $tokens->count(); $index < $count; $index++) {

            $token = $tokens[ $index ];

            if ($token->equals('=')) {

                $hasAssignment = true;

                continue;

            }

            if ($token->equals(';')) {
                return $hasAssignment ? $index : null;
            }

            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {
                $index = $tokens->findBlockEnd($block[ 'type' ], $index);
            }

        }

        return null;
    }

    private function findEndIncludingTrailingComment(Tokens $tokens, int $end): int
    {
        $next = $tokens->getNextNonWhitespace($end);

        if ($next === null || !$tokens[ $next ]->isComment()) {
            return $end;
        }

        $whitespace = $end + 1;

        if (!$tokens[ $whitespace ]->isWhitespace() || str_contains($tokens[ $whitespace ]->getContent(), "\n")) {
            return $end;
        }

        return $next;
    }

    private function findStartIncludingComments(Tokens $tokens, int $start): int
    {
        while (true) {

            $comment = $tokens->getPrevNonWhitespace($start);

            if ($comment === null || !$tokens[ $comment ]->isComment()) {
                return $start;
            }

            $whitespaceBeforeComment = $comment - 1;

            if (
                !$tokens[ $whitespaceBeforeComment ]->isWhitespace()
                || !str_contains($tokens[ $whitespaceBeforeComment ]->getContent(), "\n")
            ) {
                return $start;
            }

            $start = $comment;

        }
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

    private function ensureBlankLineAfter(Tokens $tokens, int $end): void
    {
        $whitespace = $end + 1;

        if ($whitespace >= $tokens->count()) {
            return;
        }

        if (!$tokens[ $whitespace ]->isWhitespace()) {

            $tokens->insertAt($whitespace, new Token([ T_WHITESPACE, $this->lineEnding() . $this->lineEnding() ]));

            return;

        }

        $content = $tokens[ $whitespace ]->getContent();

        if (substr_count($content, "\n") === 0) {

            $content = rtrim($content, " \t") . $this->lineEnding() . $this->lineEnding();

        } else if (substr_count($content, "\n") === 1) {

            $content = $this->lineEnding() . $content;

        }

        $tokens[ $whitespace ] = new Token([ T_WHITESPACE, $content ]);
    }

    private function ensureBlankLineBefore(Tokens $tokens, int $start): void
    {
        $whitespace = $start - 1;

        if ($whitespace < 0) {
            return;
        }

        if (!$tokens[ $whitespace ]->isWhitespace()) {

            $tokens->insertAt($start, new Token([ T_WHITESPACE, $this->lineEnding() . $this->lineEnding() ]));

            return;

        }

        $content = $tokens[ $whitespace ]->getContent();

        if (substr_count($content, "\n") === 0) {

            $content = $this->lineEnding() . $this->lineEnding() . $content;

        } else if (substr_count($content, "\n") === 1) {

            $content = $this->lineEnding() . $content;

        }

        $tokens[ $whitespace ] = new Token([ T_WHITESPACE, $content ]);
    }

    private function lineEnding(): string
    {
        return $this->whitespacesConfig->getLineEnding();
    }
}
