<?php

namespace App\Filament\Exports;

use Illuminate\Support\Facades\Auth;
use Filament\Actions\Exports\Exporter;
use Filament\Notifications\Notification;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class SalesTransactionExporter extends Exporter
{
    protected static ?string $model = SalesTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('customer.name')
                ->label('Customer'),
            ExportColumn::make('transaction_date')
                ->label('Transaction Date'),
            ExportColumn::make('total_amount')
                ->label('Total Amount'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('salesRep.name')
                ->label('Sales Representative'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sales transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
