<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementProject\Project;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class ProjectImporter extends Importer
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->required()
                ->numeric(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('name')
                ->required(),
            ImportColumn::make('description')
                ->nullable(),
            ImportColumn::make('start_date')
                ->required()
                ->date(),
            ImportColumn::make('end_date')
                ->nullable()
                ->date(),
            ImportColumn::make('client_id')
                ->nullable()
                ->numeric(),
            ImportColumn::make('budget')
                ->numeric()
                ->nullable(),
            ImportColumn::make('manager_id')
                ->nullable()
                ->numeric(),
            ImportColumn::make('status')
                ->required()
                ->rules(['in:planning,in_progress,completed,on_hold,cancelled'])
        ];
    }

    public function resolveRecord(): ?Project
    {
        // return Project::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Project();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
