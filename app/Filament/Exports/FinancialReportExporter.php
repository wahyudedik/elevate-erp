<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementFinancial\FinancialReport;

class FinancialReportExporter extends Exporter
{
    protected static ?string $model = FinancialReport::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company_id')
                ->label('Company ID'),
            ExportColumn::make('branch_id')
                ->label('Branch ID'),
            ExportColumn::make('report_name')
                ->label('Report Name'),
            ExportColumn::make('report_type')
                ->label('Report Type'),
            ExportColumn::make('report_period_start')
                ->label('Period Start'),
            ExportColumn::make('report_period_end')
                ->label('Period End'),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your financial report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
