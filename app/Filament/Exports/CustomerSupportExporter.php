<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementCRM\CustomerSupport;

class CustomerSupportExporter extends Exporter
{
    protected static ?string $model = CustomerSupport::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('company.name')
                ->label('Company Name'),
            ExportColumn::make('branch.name')
                ->label('Branch Name'),
            ExportColumn::make('customer.name')
                ->label('Customer Name'),
            ExportColumn::make('subject')
                ->label('Subject'),
            ExportColumn::make('description')
                ->label('Description'),
            ExportColumn::make('priority')
                ->label('Priority'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('customer_rating')
                ->label('Customer Rating'),
            ExportColumn::make('customer_satisfaction')
                ->label('Customer Satisfaction'),
            ExportColumn::make('deleted_at')
                ->label('Deleted At'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer support export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
