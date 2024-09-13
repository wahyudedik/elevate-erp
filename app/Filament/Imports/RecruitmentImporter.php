<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementSDM\Recruitment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class RecruitmentImporter extends Importer
{
    protected static ?string $model = Recruitment::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('job_title')
                ->label('Job Title'),
            ImportColumn::make('job_description')
                ->label('Job Description'),
            ImportColumn::make('employment_type')
                ->label('Employment Type'),
            ImportColumn::make('location')
                ->label('Location'),
            ImportColumn::make('posted_date')
                ->label('Posted Date'),
            ImportColumn::make('closing_date')
                ->label('Closing Date'),
            ImportColumn::make('status')
                ->label('Status'),
        ];
    }

    public function resolveRecord(): ?Recruitment
    {
        // return Recruitment::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Recruitment();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your recruitment import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
