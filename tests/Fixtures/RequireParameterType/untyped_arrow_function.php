<?php

declare(strict_types = 1);

final class MissingArrowFunctionParameterTypeFixture
{
    public function apply(object $builder): void
    {
        $builder->whereHas('roles', fn ($query) => $query->where('name', 'retailer'));
    }
}
