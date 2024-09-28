<?php

namespace App\Filament\Imports;

use App\Models\Branch;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BranchImporter extends Importer
{
    protected static ?string $model = Branch::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('address')
                ->rules(['nullable', 'string']),
            ImportColumn::make('phone')
                ->rules(['nullable', 'string']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email']),
            ImportColumn::make('description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('latitude')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('longitude')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('radius')
                ->numeric()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('status')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Branch
    {
        // return Branch::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Branch();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your branch import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
