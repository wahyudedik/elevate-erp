<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementSDM\CandidateInterview;

class CandidateInterviewExporter extends Exporter
{
    protected static ?string $model = CandidateInterview::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('candidate.name')
                ->label('Candidate'),
            ExportColumn::make('interview_date')
                ->label('Interview Date'),
            ExportColumn::make('interviewer.first_name')
                ->label('Interviewer'),
            ExportColumn::make('interview_type')
                ->label('Interview Type'),
            ExportColumn::make('interview_notes')
                ->label('Interview Notes'),
            ExportColumn::make('result')
                ->label('Result'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your candidate interview export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
