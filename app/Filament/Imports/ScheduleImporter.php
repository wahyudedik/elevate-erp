<?php

namespace App\Filament\Imports;

use App\Models\ManagementSDM\Schedule;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class ScheduleImporter extends Importer
{
    protected static ?string $model = Schedule::class;

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
            ImportColumn::make('employee_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('shift_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('date')
                ->required()
                ->date(),
            ImportColumn::make('is_wfa')
                ->boolean()
                ->default(false),
            ImportColumn::make('is_banned')
                ->boolean()
                ->default(false),
        ];
    }

    public function resolveRecord(): ?Schedule
    {
        // return Schedule::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Schedule();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your schedule import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
