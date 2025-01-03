<?php

namespace App\Filament\Imports;

use App\Models\Department;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DepartmentImporter extends Importer
{
    protected static ?string $model = Department::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description')
                ->rules(['nullable']),
            ImportColumn::make('created_at')
                ->rules(['nullable', 'date_format:Y-m-d H:i:s']),
            ImportColumn::make('updated_at')
                ->rules(['nullable', 'date_format:Y-m-d H:i:s']),
            ImportColumn::make('deleted_at')
                ->rules(['nullable', 'date_format:Y-m-d H:i:s']),
        ];
    }

    public function resolveRecord(): ?Department
    {
        // return Department::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Department();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your department import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
