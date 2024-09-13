<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Models\ManagementCRM\TicketResponse;
use Filament\Tables\Columns\Column;

class TicketResponseExporter extends Exporter
{
    protected static ?string $model = TicketResponse::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ticket_id')
                ->label('Ticket ID'),
            ExportColumn::make('response')
                ->label('Response'),
            ExportColumn::make('employee.first_name')
                ->label('Employee'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ticket response export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
