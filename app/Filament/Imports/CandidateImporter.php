<?php

namespace App\Filament\Imports;


use Filament\Actions\Imports\Importer;
use App\Models\ManagementSDM\Candidate;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class CandidateImporter extends Importer
{
    protected static ?string $model = Candidate::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id'),
            ImportColumn::make('first_name'),
            ImportColumn::make('last_name'),
            ImportColumn::make('email'),
            ImportColumn::make('phone'),
            ImportColumn::make('date_of_birth'),
            ImportColumn::make('gender'),
            ImportColumn::make('national_id_number'),
            ImportColumn::make('position_applied'),
            ImportColumn::make('status'),
            ImportColumn::make('recruiter_id'),
            ImportColumn::make('application_date'),
            ImportColumn::make('resume'),
            ImportColumn::make('address'),
            ImportColumn::make('city'),
            ImportColumn::make('state'),
            ImportColumn::make('postal_code'),
            ImportColumn::make('country'),
            ImportColumn::make('created_at'),
            ImportColumn::make('updated_at'),
        ];
    }

    public function resolveRecord(): ?Candidate
    {
        // return Candidate::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Candidate();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your candidate import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
