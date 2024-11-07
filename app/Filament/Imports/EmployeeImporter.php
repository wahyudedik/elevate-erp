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
            ImportColumn::make('user_id')
                ->numeric(),
            ImportColumn::make('company_id')
                ->numeric()
                ->required(),
            ImportColumn::make('branch_id')
                ->numeric(),
            ImportColumn::make('first_name')
                ->required(),
            ImportColumn::make('last_name')
                ->required(),
            ImportColumn::make('employee_code')
                ->required()
                ->unique(),
            ImportColumn::make('email')
                ->required()
                ->unique(),
            ImportColumn::make('phone'),
            ImportColumn::make('date_of_birth')
                ->date(),
            ImportColumn::make('gender')
                ->acceptsOnly(['male', 'female', 'other']),
            ImportColumn::make('national_id_number')
                ->unique(),
            ImportColumn::make('position_id')
                ->numeric(),
            ImportColumn::make('department_id')
                ->numeric(),
            ImportColumn::make('date_of_joining')
                ->date()
                ->required(),
            ImportColumn::make('salary')
                ->numeric(),
            ImportColumn::make('employment_status')
                ->acceptsOnly(['permanent', 'contract', 'internship'])
                ->default('permanent'),
            ImportColumn::make('manager_id')
                ->numeric(),
            ImportColumn::make('address'),
            ImportColumn::make('city'),
            ImportColumn::make('state'),
            ImportColumn::make('postal_code'),
            ImportColumn::make('country'),
            ImportColumn::make('status')
                ->acceptsOnly(['active', 'inactive', 'terminated', 'resigned'])
                ->default('active'),
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
