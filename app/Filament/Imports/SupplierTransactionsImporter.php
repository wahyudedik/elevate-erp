<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementStock\SupplierTransactions;

class SupplierTransactionsImporter extends Importer
{
    protected static ?string $model = SupplierTransactions::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['nullable', 'exists:branches,id']),
            ImportColumn::make('supplier_id')
                ->numeric()
                ->rules(['nullable', 'exists:suppliers,id']),
            ImportColumn::make('transaction_code')
                ->requiredMapping()
                ->rules(['required', 'string', 'unique:supplier_transactions,transaction_code']),
            ImportColumn::make('transaction_type')
                ->requiredMapping()
                ->rules(['required', 'in:purchase_order,payment,refund']),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('transaction_date')
                ->requiredMapping()
                ->date()
                ->rules(['required', 'date']),
            ImportColumn::make('payment_date')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('due_date')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('notes')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?SupplierTransactions
    {
        // return SupplierTransactions::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new SupplierTransactions();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your supplier transactions import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
