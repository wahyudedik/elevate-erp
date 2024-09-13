<?php

namespace App\Filament\Exports;


use Filament\Actions\Exports\Exporter;
use App\Models\ManagementSDM\Attendance;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class AttendanceExporter extends Exporter
{
    protected static ?string $model = Attendance::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('employee.name')
                ->label('Employee Name'),
            ExportColumn::make('date')
                ->label('Date'),
            ExportColumn::make('check_in')
                ->label('Check In'),
            ExportColumn::make('check_out')
                ->label('Check Out'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('note')
                ->label('Note'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your attendance export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
