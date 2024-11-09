<?php

namespace App\Filament\Imports;

use App\Models\ManagementSDM\Leave;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class LeaveImporter extends Importer
{
    protected static ?string $model = Leave::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->required()
                ->numeric(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('user_id')
                ->required()
                ->numeric(),
            ImportColumn::make('start_date')
                ->required()
                ->date(),
            ImportColumn::make('end_date')
                ->required()
                ->date(),
            ImportColumn::make('reason')
                ->required(),
            ImportColumn::make('status')
                ->required()
                ->acceptsOnly(['pending', 'approved', 'rejected']),
            ImportColumn::make('note')
                ->nullable()
        ];
    }

    public function resolveRecord(): ?Leave
    {
        // return Leave::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Leave();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your leave import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
