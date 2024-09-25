<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\JournalEntry;

class JournalEntryImporter extends Importer
{
    protected static ?string $model = JournalEntry::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->type('integer'),
            ImportColumn::make('branch')
                ->type('string')
                ->rules(['nullable']),
            ImportColumn::make('entry_date')
                ->type('date')
                ->required(),
            ImportColumn::make('description')
                ->type('string')
                ->rules(['nullable']),
            ImportColumn::make('entry_type')
                ->type('select')
                ->options(['debit', 'credit'])
                ->required(),
            ImportColumn::make('amount')
                ->type('decimal')
                ->required(),
            ImportColumn::make('account_id')
                ->type('select')
                ->relationship('account', 'id')
                ->rules(['nullable', 'exists:accounts,id']),
            ImportColumn::make('created_at')
                ->type('datetime')
                ->rules(['nullable']),
            ImportColumn::make('updated_at')
                ->type('datetime')
                ->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?JournalEntry
    {
        // return JournalEntry::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new JournalEntry();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your journal entry import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
