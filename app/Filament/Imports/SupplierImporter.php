<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementStock\Supplier;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class SupplierImporter extends Importer
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('supplier_name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('supplier_code')
                ->requiredMapping()
                ->rules(['required', 'string', 'unique:suppliers,supplier_code']),
            ImportColumn::make('contact_name')
                ->rules(['nullable', 'string']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'unique:suppliers,email']),
            ImportColumn::make('phone')
                ->rules(['nullable', 'string']),
            ImportColumn::make('fax')
                ->rules(['nullable', 'string']),
            ImportColumn::make('website')
                ->rules(['nullable', 'url']),
            ImportColumn::make('tax_identification_number')
                ->rules(['nullable', 'string']),
            ImportColumn::make('address')
                ->rules(['nullable', 'string']),
            ImportColumn::make('city')
                ->rules(['nullable', 'string']),
            ImportColumn::make('state')
                ->rules(['nullable', 'string']),
            ImportColumn::make('postal_code')
                ->rules(['nullable', 'string']),
            ImportColumn::make('country')
                ->rules(['nullable', 'string']),
            ImportColumn::make('status')
                ->rules(['nullable', 'in:active,inactive'])
                ->default('active'),
            ImportColumn::make('credit_limit')
                ->rules(['nullable', 'numeric', 'min:0'])
                ->castToDecimal(2),
        ];
    }

    public function resolveRecord(): ?Supplier
    {
        // return Supplier::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Supplier();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your supplier import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
