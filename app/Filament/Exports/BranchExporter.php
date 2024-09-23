<?php

namespace App\Filament\Exports;

use App\Models\Branch;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BranchExporter extends Exporter
{
    protected static ?string $model = Branch::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('company_id'),
            ExportColumn::make('name'),
            ExportColumn::make('address'),
            ExportColumn::make('phone'),
            ExportColumn::make('email'),
            ExportColumn::make('description'),
            ExportColumn::make('latitude'),
            ExportColumn::make('longitude'),
            ExportColumn::make('radius'),
            ExportColumn::make('status'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your branch export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
