<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementFinancial\Accounting;

class AccountingImporter extends Importer
{
    protected static ?string $model = Accounting::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID')
                ->rules(['required', 'integer']),
            ImportColumn::make('company_id')
                ->label('Company')
                ->rules(['required', 'integer']),
            ImportColumn::make('branch_id')
                ->label('Branch')
                ->rules(['required', 'integer']),
            ImportColumn::make('account_name')
                ->label('Account Name')
                ->rules(['required', 'string']),
            ImportColumn::make('account_number')
                ->label('Account Number')
                ->rules(['required', 'string', 'unique:accounts,account_number']),
            ImportColumn::make('account_type')
                ->label('Account Type')
                ->rules(['required', 'string', 'in:asset,liability,equity,revenue,expense,kas']),
            ImportColumn::make('initial_balance')
                ->label('Initial Balance')
                ->rules(['required', 'numeric', 'decimal:0,2']),
            ImportColumn::make('current_balance')
                ->label('Current Balance')
                ->rules(['required', 'numeric', 'decimal:0,2']),
            ImportColumn::make('deleted_at')
                ->label('Deleted At')
                ->rules(['nullable', 'date']),
            ImportColumn::make('created_at')
                ->label('Created At')
                ->rules(['required', 'date']),
            ImportColumn::make('updated_at')
                ->label('Updated At')
                ->rules(['required', 'date']),
        ];
    }

    public function resolveRecord(): ?Accounting
    {
        // return Accounting::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Accounting();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your accounting import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
