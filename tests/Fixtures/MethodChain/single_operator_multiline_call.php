<?php

declare(strict_types = 1);

final class UserCreator
{
    public function configure(): void
    {
        $this->configureUser(
            name: 'Test User',
            email: 'test@example.com',
        );
    }
}
