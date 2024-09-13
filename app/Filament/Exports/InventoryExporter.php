<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use App\Models\ManagementStock\Inventory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class InventoryExporter extends Exporter
{
    protected static ?string $model = Inventory::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('item_name'),
            ExportColumn::make('sku'),
            ExportColumn::make('quantity'),
            ExportColumn::make('purchase_price'),
            ExportColumn::make('selling_price'),
            ExportColumn::make('location'),
            ExportColumn::make('supplier.name')->label('Supplier'),
            ExportColumn::make('status'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your inventory export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
