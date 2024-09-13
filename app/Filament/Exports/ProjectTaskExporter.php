<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementProject\ProjectTask;

class ProjectTaskExporter extends Exporter
{
    protected static ?string $model = ProjectTask::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('project.name')
                ->label('Project Name'),
            ExportColumn::make('task_name')
                ->label('Task Name'),
            ExportColumn::make('task_description')
                ->label('Description'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('assigned_to.name')
                ->label('Assigned To'),
            ExportColumn::make('due_date')
                ->label('Due Date'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your project task export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
