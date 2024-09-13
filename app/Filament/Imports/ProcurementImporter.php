<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use App\Models\ManagementStock\Procurement;
use Filament\Actions\Imports\Models\Import;

class ProcurementImporter extends Importer
{
    protected static ?string $model = Procurement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('supplier_id')
                ->label('Supplier ID')
                ->requiredMapping()
                ->rules(['required', 'exists:suppliers,id']),
            ImportColumn::make('procurement_date')
                ->label('Procurement Date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('total_cost')
                ->label('Total Cost')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['required', 'in:ordered,received,cancelled']),
        ];
    }

    public function resolveRecord(): ?Procurement
    {
        // return Procurement::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Procurement();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your procurement import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
