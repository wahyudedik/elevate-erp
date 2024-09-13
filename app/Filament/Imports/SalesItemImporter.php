<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSalesAndPurchasing\SalesItem;

class SalesItemImporter extends Importer
{
    protected static ?string $model = SalesItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sales_transaction_id')
                ->numeric()
                ->required(),
            ImportColumn::make('product_name')
                ->rules(['required', 'string']),
            ImportColumn::make('quantity')
                ->numeric()
                ->required(),
            ImportColumn::make('unit_price')
                ->numeric()
                ->rules(['required', 'decimal:0,2']),
            ImportColumn::make('total_price')
                ->numeric()
                ->rules(['required', 'decimal:0,2']),
        ];
    }

    public function resolveRecord(): ?SalesItem
    {
        // return SalesItem::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new SalesItem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sales item import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
