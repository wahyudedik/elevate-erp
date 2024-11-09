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
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('user.name')
                ->label('User'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('employee.name')
                ->label('Employee'),
            ExportColumn::make('schedule.name')
                ->label('Schedule'),
            ExportColumn::make('date')
                ->label('Date'),
            ExportColumn::make('schedules_check_in')
                ->label('Schedule Check In'),
            ExportColumn::make('schedules_check_out')
                ->label('Schedule Check Out'),
            ExportColumn::make('schedules_latitude')
                ->label('Schedule Latitude'),
            ExportColumn::make('schedules_longitude')
                ->label('Schedule Longitude'),
            ExportColumn::make('check_in')
                ->label('Check In'),
            ExportColumn::make('check_out')
                ->label('Check Out'),
            ExportColumn::make('latitude_check_in')
                ->label('Latitude Check In'),
            ExportColumn::make('longitude_check_in')
                ->label('Longitude Check In'),
            ExportColumn::make('latitude_check_out')
                ->label('Latitude Check Out'),
            ExportColumn::make('longitude_check_out')
                ->label('Longitude Check Out'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('note')
                ->label('Note'),
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
        $body = 'Your attendance export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
