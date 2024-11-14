<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementCRM\CustomerSupport;

class CustomerSupportImporter extends Importer
{
    protected static ?string $model = CustomerSupport::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['exists:companies,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->rules(['exists:branches,id']),
            ImportColumn::make('customer_id')
                ->numeric()
                ->rules(['exists:customers,id']),
            ImportColumn::make('subject')
                ->requiredMapping()
                ->rules(['string', 'max:255']),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['string']),
            ImportColumn::make('priority')
                ->requiredMapping()
                ->rules(['in:low,medium,high']),
            ImportColumn::make('status')
                ->rules(['in:open,in_progress,resolved,closed'])
                ->default('open'),
            ImportColumn::make('customer_rating')
                ->numeric()
                ->rules(['numeric', 'min:1', 'max:5']),
            ImportColumn::make('customer_satisfaction')
                ->rules(['in:satisfied,neutral,dissatisfied']),
        ];
    }

    public function resolveRecord(): ?CustomerSupport
    {
        // return CustomerSupport::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new CustomerSupport();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer support import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
