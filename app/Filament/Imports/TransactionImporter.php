<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\Transaction;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->numeric()
                ->rules(['required', 'integer', 'exists:transactions,id']),
            ImportColumn::make('ledger_id')
                ->numeric()
                ->rules(['nullable', 'exists:ledgers,id']),
            ImportColumn::make('transaction_number')
                ->rules(['required', 'string', 'unique:transactions,transaction_number']),
            ImportColumn::make('status')
                ->rules(['required', 'in:pending,completed,failed']),
            ImportColumn::make('amount')
                ->numeric()
                ->rules(['required', 'numeric', 'decimal:0,2']),
            ImportColumn::make('notes')
                ->rules(['nullable', 'string']),
            ImportColumn::make('created_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->date()
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        // return Transaction::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Transaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
