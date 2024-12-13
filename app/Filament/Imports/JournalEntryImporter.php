<?php

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\Company;
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
                ->numeric(),
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'exists:companies,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['nullable', 'integer', 'exists:branches,id']),
            ImportColumn::make('entry_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('entry_type')
                ->requiredMapping()
                ->rules(['required', 'in:debit,credit']),
            ImportColumn::make('amount')
                ->numeric()
                ->requiredMapping()
                ->rules(['required', 'numeric']),
            ImportColumn::make('account_id')
                ->numeric()
                ->rules(['nullable', 'integer', 'exists:accounts,id']),
            ImportColumn::make('deleted_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('created_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->rules(['nullable', 'date']),
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
