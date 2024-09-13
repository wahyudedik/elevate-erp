<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class SalesTransactionImporter extends Importer
{
    protected static ?string $model = SalesTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer_id')
                ->label('Customer ID')
                ->requiredMapping()
                ->rules(['required', 'exists:customers,id']),
            ImportColumn::make('transaction_date')
                ->label('Transaction Date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('total_amount')
                ->label('Total Amount')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0', 'max:999999999999999.99']),
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['required', 'in:pending,completed,cancelled']),
            ImportColumn::make('sales_rep_id')
                ->label('Sales Representative ID')
                ->requiredMapping()
                ->rules(['required', 'exists:employees,id']),
        ];
    }

    public function resolveRecord(): ?SalesTransaction
    {
        // return SalesTransaction::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new SalesTransaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sales transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
