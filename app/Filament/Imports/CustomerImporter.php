<?php

namespace App\Filament\Imports;

use App\Models\ManagementCRM\Customer;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

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
                ->required(),
            ImportColumn::make('email')
                ->required()
                ->rules(['email', 'unique:customers,email']),
            ImportColumn::make('phone')
                ->nullable(),
            ImportColumn::make('address')
                ->nullable(),
            ImportColumn::make('company')
                ->nullable(),
            ImportColumn::make('status')
                ->required()
                ->rules(['in:active,inactive'])
                ->default('active')
        ];
    }

    public function resolveRecord(): ?Customer
    {
        // return Customer::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Customer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
