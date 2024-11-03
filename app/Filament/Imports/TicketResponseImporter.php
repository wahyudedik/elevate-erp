<?php

namespace App\Filament\Imports;

use App\Models\ManagementSDM\Employee;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementCRM\TicketResponse;
use App\Models\ManagementCRM\CustomerSupport;

class TicketResponseImporter extends Importer
{
    protected static ?string $model = TicketResponse::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id'),
            ImportColumn::make('company_id')
                ->required()
                ->relationship(),
            ImportColumn::make('branch_id')
                ->relationship(),
            ImportColumn::make('ticket_id'),
            ImportColumn::make('response')
                ->required(),
            ImportColumn::make('employee_id'),
        ];
    }

    public function resolveRecord(): ?TicketResponse
    {
        // return TicketResponse::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new TicketResponse();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your ticket response import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
