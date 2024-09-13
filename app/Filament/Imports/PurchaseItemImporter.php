<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSalesAndPurchasing\PurchaseItem;

class PurchaseItemImporter extends Importer
{
    protected static ?string $model = PurchaseItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('purchase_transaction_id')
                ->label('Purchase Transaction ID')
                ->rules(['required', 'integer']),

            ImportColumn::make('product_name')
                ->label('Product Name')
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('quantity')
                ->label('Quantity')
                ->numeric()
                ->rules(['required', 'integer', 'min:1']),

            ImportColumn::make('unit_price')
                ->label('Unit Price')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('total_price')
                ->label('Total Price')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?PurchaseItem
    {
        // return PurchaseItem::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new PurchaseItem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your purchase item import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
