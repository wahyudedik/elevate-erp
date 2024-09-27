<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementFinancial\BalanceSheet;

class BalanceSheetExporter extends Exporter
{
    protected static ?string $model = BalanceSheet::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('company_id'),
            ExportColumn::make('branch'),
            ExportColumn::make('financial_report_id'),
            ExportColumn::make('total_assets'),
            ExportColumn::make('total_liabilities'),
            ExportColumn::make('total_equity'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your balance sheet export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
