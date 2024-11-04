<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use App\Models\ManagementCRM\OrderItem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class OrderItemImporter extends Importer
{
    protected static ?string $model = OrderItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->numeric()
                ->required(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('order_id')
                ->numeric()
                ->nullable(),
            ImportColumn::make('product_name')
                ->rules(['required', 'string']),
            ImportColumn::make('quantity')
                ->numeric()
                ->required(),
            ImportColumn::make('unit_price')
                ->numeric()
                ->required(),
            ImportColumn::make('total_price')
                ->numeric()
                ->required(),        ];
    }

    public function resolveRecord(): ?OrderItem
    {
        // return OrderItem::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new OrderItem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your order item import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
