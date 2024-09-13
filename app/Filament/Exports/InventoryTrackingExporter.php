<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementStock\InventoryTracking;

class InventoryTrackingExporter extends Exporter
{
    protected static ?string $model = InventoryTracking::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('inventory.name')
                ->label('Inventory Name'),
            ExportColumn::make('quantity_before')
                ->label('Quantity Before'),
            ExportColumn::make('quantity_after')
                ->label('Quantity After'),
            ExportColumn::make('transaction_type')
                ->label('Transaction Type'),
            ExportColumn::make('remarks')
                ->label('Remarks'),
            ExportColumn::make('transaction_date')
                ->label('Transaction Date'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your inventory tracking export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
