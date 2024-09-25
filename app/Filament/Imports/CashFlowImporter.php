<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\CashFlow;

class CashFlowImporter extends Importer
{
    protected static ?string $model = CashFlow::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->numeric()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('company_id')
                ->numeric()
                ->rules(['required', 'exists:companies,id']),
            importColumn::make('branch_id')
                ->numeric()
                ->rules(['required', 'exists:branches,id']),
            ImportColumn::make('financial_report_id')
                ->numeric()
                ->rules(['nullable', 'exists:financial_reports,id']),
            ImportColumn::make('operating_cash_flow')
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('investing_cash_flow')
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('financing_cash_flow')
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('net_cash_flow')
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('created_at')
                ->date()
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->date()
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?CashFlow
    {
        // return CashFlow::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new CashFlow();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your cash flow import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
