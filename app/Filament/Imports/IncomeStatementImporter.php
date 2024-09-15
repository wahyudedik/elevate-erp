<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\IncomeStatement;

class IncomeStatementImporter extends Importer
{
    protected static ?string $model = IncomeStatement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('financial_report_id')
                ->numeric()
                ->rules(['nullable', 'exists:financial_reports,id']),
            ImportColumn::make('total_revenue')
                ->numeric()
                ->rules(['required', 'numeric', 'decimal:0,2']),
            ImportColumn::make('total_expenses')
                ->numeric()
                ->rules(['required', 'numeric', 'decimal:0,2']),
            ImportColumn::make('net_income')
                ->numeric()
                ->rules(['required', 'numeric', 'decimal:0,2']),
        ];
    }

    public function resolveRecord(): ?IncomeStatement
    {
        // return IncomeStatement::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new IncomeStatement();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your income statement import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
