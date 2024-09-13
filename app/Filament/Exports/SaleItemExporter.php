<?php

namespace App\Filament\Exports;

use App\Models\ManagementCRM\SaleItem;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class SaleItemExporter extends Exporter
{
    protected static ?string $model = SaleItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
            ->label('ID'),
            ExportColumn::make('sale_id')
            ->label('Sale ID'),
            ExportColumn::make('product_name')
            ->label('Product Name'),
            ExportColumn::make('quantity')
            ->label('Quantity'),
            ExportColumn::make('unit_price')
            ->label('Price'),
            ExportColumn::make('total_price')
            ->label('Total Price'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
