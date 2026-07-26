<?php

declare(strict_types = 1);

final class MissingFormException extends RuntimeException
{
    public function __construct(string $slug)
    {
        parent::__construct(sprintf('Form %s was not found.', $slug));
    }
}

final class PromotedBody
{
    public function __construct(
        public readonly string $slug,
    )
    {
        $this->validate();
    }

    private function validate(): void
    {
    }
}
