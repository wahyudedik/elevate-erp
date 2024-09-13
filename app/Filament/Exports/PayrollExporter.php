<?php

namespace App\Filament\Exports;


use App\Models\ManagementSDM\Payroll;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class PayrollExporter extends Exporter
{
    protected static ?string $model = Payroll::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('employee.name')
                ->label('Employee Name'),
            ExportColumn::make('basic_salary')
                ->label('Basic Salary'),
            ExportColumn::make('allowances')
                ->label('Allowances'),
            ExportColumn::make('deductions')
                ->label('Deductions'),
            ExportColumn::make('net_salary')
                ->label('Net Salary'),
            ExportColumn::make('payment_date')
                ->label('Payment Date'),
            ExportColumn::make('payment_status')
                ->label('Payment Status'),
            ExportColumn::make('payment_method')
                ->label('Payment Method'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payroll export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

}
