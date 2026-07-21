<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Models\User;

final class UserController
{
    public function index(): void
    {
        User::query()->get();
    }
}
