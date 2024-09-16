<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\FinancialReport;

class FinancialReportImporter extends Importer
{
    protected static ?string $model = FinancialReport::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('report_name')
                ->label('Report Name')
                ->rules(['required', 'string']),
            ImportColumn::make('report_type')
                ->label('Report Type')
                ->rules(['required', 'in:balance_sheet,income_statement,cash_flow']),
            ImportColumn::make('report_period_start')
                ->label('Report Period Start')
                ->rules(['required', 'date']),
            ImportColumn::make('report_period_end')
                ->label('Report Period End')
                ->rules(['required', 'date']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->rules(['nullable', 'string']),
            ImportColumn::make('created_at')
                ->label('Created At')
                ->rules(['nullable', 'date']),
            ImportColumn::make('updated_at')
                ->label('Updated At')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?FinancialReport
    {
        // return FinancialReport::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new FinancialReport();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your financial report import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
