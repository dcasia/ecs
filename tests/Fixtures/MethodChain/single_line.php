<?php

declare(strict_types = 1);

final class UserCreator
{
    public function create(): void
    {
        UserFactory::new()->create()->assignRole('Admin');
    }
}
