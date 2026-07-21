<?php

declare(strict_types = 1);

Schema::table('users', function (Blueprint $table): void {
    $table->string('locale', 5)->default('en')->after('password');
});
