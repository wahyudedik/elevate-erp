<?php

namespace App\Filament\Exports;

use App\Models\ManagementSDM\Shift;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ShiftExporter extends Exporter
{
    protected static ?string $model = Shift::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('name')
                ->label('Shift Name'),
            ExportColumn::make('start_time')
                ->label('Start Time'),
            ExportColumn::make('end_time')
                ->label('End Time'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your shift export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
