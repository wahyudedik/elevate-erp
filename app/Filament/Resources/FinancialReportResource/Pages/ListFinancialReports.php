<?php

namespace App\Filament\Resources\FinancialReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\FinancialReportResource;
use App\Filament\Resources\FinancialReportResource\Widgets\FinancialReport;

class ListFinancialReports extends ListRecords
{
    protected static string $resource = FinancialReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialReport::class,
        ];
    }
}
