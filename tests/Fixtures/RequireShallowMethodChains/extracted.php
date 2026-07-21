<?php

declare(strict_types = 1);

final class EntryForm
{
    public function schema(): void
    {
        $this->schema(schema: [
            Section::make(__('Entry type'))->schema(components: [
                ToggleButtons::make('entry_type')->icons($this->entryTypeIcons()),
            ]),
        ]);
    }

    private function entryTypeIcons(): array
    {
        return collect(ManualEntryType::cases())->mapWithKeys(fn (ManualEntryType $type): array => [ $type->value => $type->getIcon() ])->all();
    }
}
