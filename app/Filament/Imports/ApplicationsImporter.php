<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementSDM\Applications;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class ApplicationsImporter extends Importer
{
    protected static ?string $model = Applications::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('recruitment_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('candidate_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('status')
                ->enum(['applied', 'interviewing', 'offered', 'hired', 'rejected'])
                ->default('applied'),
            ImportColumn::make('resume')
                ->nullable(),
            ImportColumn::make('company_id')
                ->numeric()
                ->required(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
        ];
    }

    public function resolveRecord(): ?Applications
    {
        // return Applications::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Applications();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your applications import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
