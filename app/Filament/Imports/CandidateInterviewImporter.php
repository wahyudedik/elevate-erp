<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementSDM\CandidateInterview;

class CandidateInterviewImporter extends Importer
{
    protected static ?string $model = CandidateInterview::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->required()
                ->relationship(),
            ImportColumn::make('branch_id')
                ->relationship(),
            ImportColumn::make('candidate_id')
                ->relationship(),
            ImportColumn::make('interview_date')
                ->required()
                ->date(),
            ImportColumn::make('interviewer_id')
                ->relationship(),
            ImportColumn::make('interview_type')
                ->required()
                ->enum([
                    'phone' => 'Phone',
                    'video' => 'Video',
                    'in_person' => 'In Person',
                ])
                ->default('in_person'),
            ImportColumn::make('interview_notes'),
            ImportColumn::make('result')
                ->required()
                ->enum([
                    'passed' => 'Passed',
                    'failed' => 'Failed',
                    'pending' => 'Pending',
                ])
                ->default('pending')
        ];
    }

    public function resolveRecord(): ?CandidateInterview
    {
        // return CandidateInterview::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new CandidateInterview();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your candidate interview import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
