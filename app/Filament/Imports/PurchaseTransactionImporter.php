<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;

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

            ImportColumn::make('invoice_number')
                ->label('Invoice Number')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('supplier_name')
                ->label('Supplier Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('total_amount')
                ->label('Total Amount')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('payment_status')
                ->label('Payment Status')
                ->requiredMapping()
                ->rules(['required', 'string', 'in:paid,unpaid,partial']),

            ImportColumn::make('notes')
                ->label('Notes')
                ->rules(['nullable', 'string']),
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
