<?php

namespace App\Filament\Imports;

use App\Models\ManagementSDM\Shift;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class ShiftImporter extends Importer
{
    protected static ?string $model = Shift::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->required()
                ->numeric(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('name')
                ->required()
                ->string(),
            ImportColumn::make('start_time')
                ->required()
                ->time(),
            ImportColumn::make('end_time')
                ->required()
                ->time(),
            ImportColumn::make('deleted_at')
                ->nullable()
                ->date(),
            ImportColumn::make('created_at')
                ->nullable()
                ->date(),
            ImportColumn::make('updated_at')
                ->nullable()
                ->date(),
        ];
    }

    public function resolveRecord(): ?Shift
    {
        // return Shift::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Shift();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your shift import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
