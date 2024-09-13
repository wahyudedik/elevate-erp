<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementStock\InventoryTracking;

class InventoryTrackingImporter extends Importer
{
    protected static ?string $model = InventoryTracking::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('inventory_id')
                ->label('Inventory ID')
                ->required()
                ->numeric()
                ->rules(['exists:inventories,id']),
            ImportColumn::make('quantity_before')
                ->label('Quantity Before')
                ->required()
                ->numeric(),
            ImportColumn::make('quantity_after')
                ->label('Quantity After')
                ->required()
                ->numeric(),
            ImportColumn::make('transaction_type')
                ->label('Transaction Type')
                ->required()
                ->enum(['addition', 'deduction']),
            ImportColumn::make('remarks')
                ->label('Remarks')
                ->nullable(),
            ImportColumn::make('transaction_date')
                ->label('Transaction Date')
                ->required()
                ->date(),
        ];
    }

    public function resolveRecord(): ?InventoryTracking
    {
        // return InventoryTracking::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new InventoryTracking();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your inventory tracking import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
