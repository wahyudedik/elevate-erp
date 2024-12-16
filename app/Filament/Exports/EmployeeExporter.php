<?php

namespace App\Filament\Exports;


use App\Models\ManagementSDM\Employee;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('user_id'),
            ExportColumn::make('company_id'),
            ExportColumn::make('branch_id'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('employee_code'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('date_of_birth'),
            ExportColumn::make('gender'),
            ExportColumn::make('national_id_number'),
            ExportColumn::make('position_id'),
            ExportColumn::make('department_id'),
            ExportColumn::make('date_of_joining'),
            ExportColumn::make('salary'),
            ExportColumn::make('employment_status'),
            ExportColumn::make('manager_id'),
            ExportColumn::make('address'),
            ExportColumn::make('province_id'),
            ExportColumn::make('city_id'),
            ExportColumn::make('district_id'),
            ExportColumn::make('postal_code'),
            ExportColumn::make('status'),
            ExportColumn::make('profile_picture'),
            ExportColumn::make('contract'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
