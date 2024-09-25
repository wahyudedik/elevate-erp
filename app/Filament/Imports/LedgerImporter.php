<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementFinancial\Ledger;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class LedgerImporter extends Importer
{
    protected static ?string $model = Ledger::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->numeric()
                ->rules(['integer', 'exists:ledgers,id']),
            ImportColumn::make('branch')
                ->rules(['nullable', 'string']),
            ImportColumn::make('account_id')
                ->numeric()
                ->rules(['exists:accounts,id']),
            ImportColumn::make('transaction_date')
                ->date(),
            ImportColumn::make('transaction_type')
                ->rules(['in:debit,credit']),
            ImportColumn::make('amount')
                ->numeric()
                ->rules(['numeric', 'min:0']),
            ImportColumn::make('transaction_description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('created_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->date()
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?Ledger
    {
        // return Ledger::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Ledger();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your ledger import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
