<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementStock\PurchaseTransaction;

class PurchaseTransactionImporter extends Importer
{
    protected static ?string $model = PurchaseTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('transaction_date')
                ->label('Transaction Date')
                ->requiredMapping()
                ->rules(['required', 'date']),

            ImportColumn::make('company_id')
                ->label('Company')
                ->requiredMapping()
                ->rules(['required', 'exists:companies,id']),

            ImportColumn::make('branch_id')
                ->label('Branch')
                ->rules(['nullable', 'exists:branches,id']),

            ImportColumn::make('supplier_id')
                ->label('Supplier')
                ->rules(['nullable', 'exists:suppliers,id']),

            ImportColumn::make('total_amount')
                ->label('Total Amount')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['required', 'string', 'in:pending,received,cancelled']),

            ImportColumn::make('purchasing_agent_id')
                ->label('Purchasing Agent')
                ->requiredMapping()
                ->rules(['required', 'exists:employees,id']),
        ];
    }

    public function resolveRecord(): ?PurchaseTransaction
    {
        // return PurchaseTransaction::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new PurchaseTransaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your purchase transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
