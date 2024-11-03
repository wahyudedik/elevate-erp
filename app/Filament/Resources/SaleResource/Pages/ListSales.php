<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Filament\Actions;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SaleResource\Widgets\AdvancedStatsOverviewWidget;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

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
