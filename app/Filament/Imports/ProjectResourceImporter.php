<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use App\Models\ManagementProject\ProjectResource;

class ProjectResourceImporter extends Importer
{
    protected static ?string $model = ProjectResource::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company_id')
                ->numeric()
                ->required()
                ->relationship(),
            ImportColumn::make('branch_id')
                ->numeric()
                ->nullable()
                ->relationship(),
            ImportColumn::make('project_id')
                ->numeric()
                ->nullable()
                ->relationship(),
            ImportColumn::make('resource_name')
                ->rules(['required', 'string']),
            ImportColumn::make('resource_type')
                ->rules(['required', 'in:human,material,financial']),
            ImportColumn::make('resource_cost')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?ProjectResource
    {
        // return ProjectResource::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ProjectResource();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your project resource import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
