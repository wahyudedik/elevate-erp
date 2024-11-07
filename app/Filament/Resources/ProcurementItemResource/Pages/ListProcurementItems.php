<?php

namespace App\Filament\Resources\ProcurementItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProcurementItemResource;
use App\Filament\Resources\ProcurementItemResource\Widgets\AdvancedStatsOverviewWidget;

class ListProcurementItems extends ListRecords
{
    protected static string $resource = ProcurementItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
