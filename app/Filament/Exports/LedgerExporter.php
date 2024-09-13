<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use App\Models\ManagementFinancial\Ledger;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class LedgerExporter extends Exporter
{
    protected static ?string $model = Ledger::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('account_id'),
            ExportColumn::make('transaction_date'),
            ExportColumn::make('transaction_type'),
            ExportColumn::make('amount'),
            ExportColumn::make('transaction_description'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ledger export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
