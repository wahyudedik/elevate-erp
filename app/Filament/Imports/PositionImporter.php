<?php

namespace App\Filament\Imports;

use App\Models\Position;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PositionImporter extends Importer
{
    protected static ?string $model = Position::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('department_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): ?Position
    {
        // return Position::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Position();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your position import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
