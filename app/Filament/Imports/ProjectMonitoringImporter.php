<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementProject\ProjectMonitoring;

class ProjectMonitoringImporter extends Importer
{
    protected static ?string $model = ProjectMonitoring::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('project_id')
                ->numeric()
                ->required()
                ->relationship(),
            ImportColumn::make('progress_report')
                ->required(),
            ImportColumn::make('status')
                ->required()
                ->enum(['on_track', 'at_risk', 'delayed']),
            ImportColumn::make('completion_percentage')
                ->numeric()
                ->required(),
            ImportColumn::make('report_date')
                ->date()
                ->required(),
        ];
    }

    public function resolveRecord(): ?ProjectMonitoring
    {
        // return ProjectMonitoring::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProjectMonitoring();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project monitoring import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
