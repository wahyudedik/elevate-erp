<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use App\Models\ManagementStock\Procurement;
use Filament\Actions\Exports\Models\Export;

class ProcurementExporter extends Exporter
{
    protected static ?string $model = Procurement::class;

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
            ExportColumn::make('procurement_date')
                ->label('Procurement Date'),
            ExportColumn::make('total_cost')
                ->label('Total Cost'),
            ExportColumn::make('status')
                ->label('Status'),
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
        $body = 'Your procurement export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
