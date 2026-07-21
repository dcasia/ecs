<?php

declare(strict_types = 1);

$itemLabel = fn (array $state): ?string => ($state[ 'label' ] ?? __('Field')) . ' (' . match ($state[ 'type' ] ?? null) {
    'name' => __('Name'),
    'phone' => __('Phone Number'),
    'email' => __('Email'),
    'date' => __('Date'),
    'time' => __('Time (Session)'),
    'text' => __('Text Input'),
    'textarea' => __('Text Area'),
    'select' => __('Dropdown'),
    default => __('unknown'),
} . ')';
