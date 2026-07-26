<?php

declare(strict_types = 1);

final class AnswerData
{
    public function __construct(public readonly Collection $safeAnswers)
    {
    }

    public static function fromDefinition(
        array $definition,
    ): self
    {
        return new self(FormAnswersData::fromArray($definition[ 'safe_answers' ])->values());
    }

    public function withThreeValues(
        string $first,
        int $second,
        ?Collection $third,
    ): void
    {
    }
}

final class Coordinates
{
    public function __construct(int $x, int $y)
    {
    }
}

final class EmptyConstructor
{
    public function __construct()
    {
    }
}
