<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementStock\ProcurementItem;

class ProcurementItemImporter extends Importer
{
    protected static ?string $model = ProcurementItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->numeric()
                ->required()
                ->rules(['exists:companies,id']),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable()
                ->rules(['exists:branches,id']),
            ImportColumn::make('procurement_id')
                ->numeric()
                ->nullable()
                ->rules(['exists:procurements,id']),
            ImportColumn::make('item_name')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('quantity')
                ->numeric()
                ->required()
                ->rules(['integer', 'min:1']),
            ImportColumn::make('unit_price')
                ->numeric()
                ->required()
                ->rules(['numeric', 'min:0', 'decimal:0,15,2']),
            ImportColumn::make('total_price')
                ->numeric()
                ->required()
                ->rules(['numeric', 'min:0', 'decimal:0,15,2'])
        ];
    }

    public function resolveRecord(): ?ProcurementItem
    {
        // return ProcurementItem::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProcurementItem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your procurement item import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
