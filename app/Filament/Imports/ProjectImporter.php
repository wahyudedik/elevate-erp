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
            ImportColumn::make('name')
                ->label('Project Name'),
            ImportColumn::make('description')
                ->label('Description'),
            ImportColumn::make('start_date')
                ->label('Start Date'),
            ImportColumn::make('end_date')
                ->label('End Date'),
            ImportColumn::make('client_id')
                ->label('Client')
                ->relationship(),
            ImportColumn::make('budget')
                ->label('Budget')
                ->numeric(),
            ImportColumn::make('manager_id')
                ->label('Project Manager')
                ->relationship(),
            ImportColumn::make('status')
                ->label('Status')
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
