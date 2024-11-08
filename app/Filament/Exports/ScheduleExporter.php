<?php

namespace App\Filament\Exports;

use App\Models\ManagementSDM\Schedule;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ScheduleExporter extends Exporter
{
    protected static ?string $model = Schedule::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('user.name')
                ->label('User'),
            ExportColumn::make('employee.name')
                ->label('Employee'),
            ExportColumn::make('shift.name')
                ->label('Shift'),
            ExportColumn::make('date')
                ->label('Date'),
            ExportColumn::make('is_wfa')
                ->label('WFA Status')
                ->state(fn($record) => $record->is_wfa ? 'Yes' : 'No'),
            ExportColumn::make('is_banned')
                ->label('Banned Status')
                ->state(fn($record) => $record->is_banned ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
            ExportColumn::make('deleted_at')
                ->label('Deleted At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your schedule export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
