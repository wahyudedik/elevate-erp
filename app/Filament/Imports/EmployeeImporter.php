<?php

namespace App\Filament\Imports;


use App\Models\ManagementSDM\Employee;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name'),
            ImportColumn::make('last_name'),
            ImportColumn::make('employee_code'),
            ImportColumn::make('email'),
            ImportColumn::make('phone'),
            ImportColumn::make('date_of_birth'),
            ImportColumn::make('gender'),
            ImportColumn::make('national_id_number'),
            ImportColumn::make('position'),
            ImportColumn::make('department'),
            ImportColumn::make('date_of_joining'),
            ImportColumn::make('salary'),
            ImportColumn::make('employment_status'),
            ImportColumn::make('manager_id'),
            ImportColumn::make('address'),
            ImportColumn::make('city'),
            ImportColumn::make('state'),
            ImportColumn::make('postal_code'),
            ImportColumn::make('country'),
            ImportColumn::make('status'),
            ImportColumn::make('profile_picture'),
            ImportColumn::make('contract'),
        ];
    }

    public function resolveRecord(): ?Employee
    {
        // return Employee::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Employee();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
