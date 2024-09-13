<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementProject\ProjectTask;

class ProjectTaskImporter extends Importer
{
    protected static ?string $model = ProjectTask::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('project_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:projects,id']),
            ImportColumn::make('task_name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('task_description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'in:pending,in_progress,completed,overdue']),
            ImportColumn::make('assigned_to')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:employees,id']),
            ImportColumn::make('due_date')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?ProjectTask
    {
        // return ProjectTask::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProjectTask();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project task import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
