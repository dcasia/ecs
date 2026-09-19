<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer;

use DigitalCreative\ECS\Tests\Support\ReflectionUniqueRule as Unique;

final class ReportingUser
{
    public const string PERMISSION_GUARD = 'reporting';
}

final class NestedArrowRuleCalls
{
    public function component(): mixed
    {
        return TextInput::make('name')->unique(
            table: 'roles',
            column: 'name',
            ignoreRecord: true,
            modifyRuleUsing: static fn (Unique $rule): Unique => $rule->where(
                'guard_name',
                ReportingUser::PERMISSION_GUARD,
            ),
        );
    }
}
