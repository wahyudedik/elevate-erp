<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementStock\Inventory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class InventoryImporter extends Importer
{
    protected static ?string $model = Inventory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('item_name')
                ->label('Item Name')
                ->rules(['required', 'string']),
            ImportColumn::make('sku')
                ->label('SKU')
                ->rules(['required', 'string', 'unique:inventories,sku']),
            ImportColumn::make('quantity')
                ->label('Quantity')
                ->rules(['required', 'integer']),
            ImportColumn::make('purchase_price')
                ->label('Purchase Price')
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('selling_price')
                ->label('Selling Price')
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('location')
                ->label('Location')
                ->rules(['nullable', 'string']),
            ImportColumn::make('supplier_id')
                ->label('Supplier ID')
                ->rules(['required', 'exists:suppliers,id']),
            ImportColumn::make('status')
                ->label('Status')
                ->rules(['required', 'in:in_stock,out_of_stock,discontinued'])
        ];
    }

    public function resolveRecord(): ?Inventory
    {
        // return Inventory::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Inventory();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your inventory import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
