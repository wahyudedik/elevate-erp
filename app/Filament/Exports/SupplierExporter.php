<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use App\Models\ManagementStock\Supplier;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class SupplierExporter extends Exporter
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('company_id'),
            ExportColumn::make('branch_id'),
            ExportColumn::make('supplier_name'),
            ExportColumn::make('supplier_code'),
            ExportColumn::make('contact_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('fax'),
            ExportColumn::make('website'),
            ExportColumn::make('tax_identification_number'),
            ExportColumn::make('address'),
            ExportColumn::make('city'),
            ExportColumn::make('state'),
            ExportColumn::make('postal_code'),
            ExportColumn::make('country'),
            ExportColumn::make('status'),
            ExportColumn::make('credit_limit'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your supplier export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
