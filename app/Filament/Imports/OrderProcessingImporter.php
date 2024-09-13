<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSalesAndPurchasing\OrderProcessing;

class OrderProcessingImporter extends Importer
{
    protected static ?string $model = OrderProcessing::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer_id')
                ->numeric()
                ->required(),
            ImportColumn::make('order_date')
                ->date()
                ->required(),
            ImportColumn::make('total_amount')
                ->numeric()
                ->required(),
            ImportColumn::make('status')
                ->enum(['pending', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->required(),
            ImportColumn::make('sales_transaction_id')
                ->numeric()
                ->nullable(),
        ];
    }

    public function resolveRecord(): ?OrderProcessing
    {
        // return OrderProcessing::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new OrderProcessing();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your order processing import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
