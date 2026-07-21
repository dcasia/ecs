<?php

declare(strict_types = 1);

final class UserCreator
{
    public function create(): void
    {
        UserFactory::new()->create([
            'name' => 'Test User With A Deliberately Long Display Name',
            'email' => 'test-user-with-a-deliberately-long-address@example.com',
            'description' => 'This value keeps the projected method call beyond the configured line length.',
        ])->assignRole('Admin');
    }
}
