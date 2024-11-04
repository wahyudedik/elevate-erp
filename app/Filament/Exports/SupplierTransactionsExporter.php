<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementStock\SupplierTransactions;

class SupplierTransactionsExporter extends Exporter
{
    protected static ?string $model = SupplierTransactions::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('supplier.name')
                ->label('Supplier'),
            ExportColumn::make('transaction_code')
                ->label('Transaction Code'),
            ExportColumn::make('transaction_type')
                ->label('Transaction Type'),
            ExportColumn::make('amount')
                ->label('Amount'),
            ExportColumn::make('transaction_date')
                ->label('Transaction Date'),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('deleted_at')
                ->label('Deleted At'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your supplier transactions export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
