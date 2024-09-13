<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementSalesAndPurchasing\OrderItem;

class OrderItemExporter extends Exporter
{
    protected static ?string $model = OrderItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('order_id')
                ->label('Order ID'),
            ExportColumn::make('product_name')
                ->label('Product Name'),
            ExportColumn::make('quantity')
                ->label('Quantity'),
            ExportColumn::make('unit_price')
                ->label('Unit Price'),
            ExportColumn::make('total_price')
                ->label('Total Price'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
