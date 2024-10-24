<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementCRM\CustomerInteraction;

class CustomerInteractionImporter extends Importer
{
    protected static ?string $model = CustomerInteraction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'unique:customer_interactions,id']),
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:companies,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['nullable', 'exists:branches,id']),
            ImportColumn::make('customer_id')
                ->numeric()
                ->rules(['nullable', 'exists:customers,id']),
            ImportColumn::make('interaction_date')
                ->requiredMapping()
                ->date(),
            ImportColumn::make('interaction_type')
                ->requiredMapping()
                ->json(),
            ImportColumn::make('details')
                ->rules(['nullable', 'string']),
            ImportColumn::make('deleted_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('created_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->date()
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?CustomerInteraction
    {
        // return CustomerInteraction::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new CustomerInteraction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer interaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
