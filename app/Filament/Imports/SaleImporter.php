<?php

namespace App\Filament\Imports;

use App\Models\ManagementCRM\Sale;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class SaleImporter extends Importer
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer_id')
                ->label('Customer ID')
                ->requiredMapping()
                ->rules(['required', 'exists:customers,id']),
            ImportColumn::make('sale_date')
                ->label('Sale Date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('total_amount')
                ->label('Total Amount')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0', 'max:999999999999999.99']),
            ImportColumn::make('status')
                ->label('Status')
                ->rules(['nullable', 'in:pending,completed,cancelled'])
                ->default('pending'),
        ];
    }

    public function resolveRecord(): ?Sale
    {
        // return Sale::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Sale();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sale import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
