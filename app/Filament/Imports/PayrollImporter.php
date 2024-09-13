<?php

namespace App\Filament\Imports;


use App\Models\ManagementSDM\Payroll;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class PayrollImporter extends Importer
{
    protected static ?string $model = Payroll::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee_id')
                ->label('Employee ID')
                ->requiredMapping()
                ->rules(['required', 'exists:employees,id']),

            ImportColumn::make('basic_salary')
                ->label('Basic Salary')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('allowances')
                ->label('Allowances')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('deductions')
                ->label('Deductions')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('net_salary')
                ->label('Net Salary')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('payment_date')
                ->label('Payment Date')
                ->requiredMapping()
                ->date()
                ->rules(['required', 'date']),

            ImportColumn::make('payment_status')
                ->label('Payment Status')
                ->requiredMapping()
                ->rules(['required', 'in:pending,paid']),

            ImportColumn::make('payment_method')
                ->label('Payment Method')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Payroll
    {
        // return Payroll::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Payroll();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your payroll import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
