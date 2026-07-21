<?php

declare(strict_types = 1);

final class EntryForm
{
    public function schema(): Section
    {
        return Section::make(__('Entry type'))
            ->description(__('Choose the current qualification stage. Failed and order statuses are only available through a later status change.'))
            ->icon('heroicon-o-adjustments-horizontal')
            ->compact()
            ->schema(components: [
                ToggleButtons::make('entry_type')
                    ->hiddenLabel()
                    ->options(fn (): array => self::availableEntryTypes())
                    ->grouped()
                    ->live()
                    ->required(),
            ]);
    }
}
