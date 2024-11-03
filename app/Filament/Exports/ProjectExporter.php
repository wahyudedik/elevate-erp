<?php

namespace App\Filament\Exports;

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Exports\Exporter;
use Filament\Notifications\Notification;
use App\Models\ManagementProject\Project;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Auth\Authenticatable as User;

class ProjectExporter extends Exporter
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('name')
                ->label('Project Name'),
            ExportColumn::make('description')
                ->label('Description'),
            ExportColumn::make('start_date')
                ->label('Start Date'),
            ExportColumn::make('end_date')
                ->label('End Date'),
            ExportColumn::make('client.name')
                ->label('Client'),
            ExportColumn::make('budget')
                ->label('Budget'),
            ExportColumn::make('manager.name')
                ->label('Project Manager'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your project export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
