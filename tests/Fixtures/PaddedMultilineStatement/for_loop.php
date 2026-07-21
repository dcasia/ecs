<?php

declare(strict_types = 1);

final class ForLoopAssignmentFixture
{
    public function update(array $tokens, int $previous, int $index): void
    {
        for ($whitespace = $previous + 1; $whitespace < $index; $whitespace++) {

            $tokens[ $index ] = new Token(
                $tokens[ $whitespace ],
            );

        }
    }
}
