<?php

namespace App\Filament\Imports;


use Filament\Actions\Imports\Importer;
use App\Models\ManagementSDM\Attendance;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class AttendanceImporter extends Importer
{
    protected static ?string $model = Attendance::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:users,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['nullable', 'exists:branches,id']),
            ImportColumn::make('employee_id')
                ->numeric()
                ->rules(['nullable', 'exists:employees,id']),
            ImportColumn::make('schedule_id')
                ->numeric()
                ->rules(['nullable', 'exists:schedules,id']),
            ImportColumn::make('date')
                ->requiredMapping()
                ->date()
                ->rules(['required', 'date']),
            ImportColumn::make('schedules_check_in')
                ->time()
                ->rules(['nullable', 'date_format:H:i:s']),
            ImportColumn::make('schedules_check_out')
                ->time()
                ->rules(['nullable', 'date_format:H:i:s']),
            ImportColumn::make('schedules_latitude')
                ->numeric()
                ->rules(['required']),
            ImportColumn::make('schedules_longitude')
                ->numeric()
                ->rules(['required']),
            ImportColumn::make('check_in')
                ->time()
                ->rules(['nullable', 'date_format:H:i:s']),
            ImportColumn::make('check_out')
                ->time()
                ->rules(['nullable', 'date_format:H:i:s']),
            ImportColumn::make('latitude_check_in')
                ->numeric()
                ->rules(['nullable']),
            ImportColumn::make('longitude_check_in')
                ->numeric()
                ->rules(['nullable']),
            ImportColumn::make('latitude_check_out')
                ->numeric()
                ->rules(['nullable']),
            ImportColumn::make('longitude_check_out')
                ->numeric()
                ->rules(['nullable']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'in:present,absent,late,on_leave']),
            ImportColumn::make('note')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Attendance
    {
        // return Attendance::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Attendance();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your attendance import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
