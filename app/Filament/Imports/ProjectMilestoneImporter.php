<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementProject\ProjectMilestone;

class ProjectMilestoneImporter extends Importer
{
    protected static ?string $model = ProjectMilestone::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->numeric()
                ->required(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('project_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('milestone_name')
                ->rules(['required', 'string']),
            ImportColumn::make('milestone_description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('milestone_date')
                ->date()
                ->required(),
            ImportColumn::make('status')
                ->rules(['required', 'in:pending,achieved'])
                ->default('pending'),        ];
    }

    public function resolveRecord(): ?ProjectMilestone
    {
        // return ProjectMilestone::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProjectMilestone();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project milestone import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
