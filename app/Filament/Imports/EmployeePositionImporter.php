<?php

namespace App\Filament\Imports;


use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSDM\EmployeePosition;

class EmployeePositionImporter extends Importer
{
    protected static ?string $model = EmployeePosition::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id'),
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('branch_id')
                ->rules(['nullable', 'exists:branches,id']),
            ImportColumn::make('employee_id')
                ->rules(['nullable', 'exists:employees,id']),
            ImportColumn::make('department')
                ->requiredMapping()
                ->rules(['required', 'json']),
            ImportColumn::make('position')
                ->requiredMapping()
                ->rules(['required', 'json']),
            ImportColumn::make('start_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('end_date')
                ->rules(['nullable', 'date', 'after:start_date']),
        ];
    }

    public function resolveRecord(): ?EmployeePosition
    {
        // return EmployeePosition::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new EmployeePosition();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee position import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
