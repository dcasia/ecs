<?php

declare(strict_types = 1);

final class ContactForm
{
    public function field(): TextInput
    {
        return TextInput::make('name')
            ->label(__('Customer name'))
            ->required()
            ->maxLength(255);
    }
}
