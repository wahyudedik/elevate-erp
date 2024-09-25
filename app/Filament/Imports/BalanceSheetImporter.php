<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\BalanceSheet;

class BalanceSheetImporter extends Importer
{
    protected static ?string $model = BalanceSheet::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id'),
            ImportColumn::make('company_id'),
            ImportColumn::make('branch')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('financial_report_id')
                ->numeric()
                ->rules(['nullable', 'exists:financial_reports,id']),
            ImportColumn::make('total_assets')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('total_liabilities')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('total_equity')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('created_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->date()
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?BalanceSheet
    {
        // return BalanceSheet::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new BalanceSheet();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your balance sheet import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
