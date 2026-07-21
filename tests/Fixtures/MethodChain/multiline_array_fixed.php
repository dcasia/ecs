<?php

declare(strict_types = 1);

final class UserCreator
{
    public function create(): void
    {
        UserFactory::new()
            ->create([ 'name' => 'Test User', 'email' => 'test@example.com', ])
            ->assignRole('Admin');
    }
}
