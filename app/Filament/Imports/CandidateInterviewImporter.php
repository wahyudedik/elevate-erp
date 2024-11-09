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
                ->label('Company ID')
                ->requiredMapping()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('branch_id')
                ->label('Branch ID')
                ->rules(['nullable', 'exists:branches,id']),
            ImportColumn::make('candidate_id')
                ->label('Candidate ID')
                ->rules(['nullable', 'exists:candidates,id']),
            ImportColumn::make('interview_date')
                ->label('Interview Date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('interviewer')
                ->label('Interviewer')
                ->rules(['nullable', 'string']),
            ImportColumn::make('interview_type')
                ->label('Interview Type')
                ->rules(['required', 'in:phone,video,in_person'])
                ->default('in_person'),
            ImportColumn::make('interview_notes')
                ->label('Interview Notes')
                ->rules(['nullable', 'string']),
            ImportColumn::make('result')
                ->label('Result')
                ->rules(['required', 'in:passed,failed,pending'])
                ->default('pending'),
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
