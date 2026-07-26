<?php

declare(strict_types = 1);

final class MissingFormException extends RuntimeException
{
    public function __construct(
        string $slug,
    )
    {
        parent::__construct(sprintf('Form %s was not found.', $slug));
    }
}

final class ProviderNotRegisteredException extends RuntimeException
{
    public function __construct(
        public readonly string $providerKey,
    )
    {
        parent::__construct(sprintf('Form provider "%s" is not registered.', $providerKey));
    }
}
