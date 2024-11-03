<?php

namespace App\Filament\Resources\SaleItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SaleItemResource;
use App\Filament\Resources\SaleItemResource\Widgets\AdvancedStatsOverviewWidget;

class ListSaleItems extends ListRecords
{
    protected static string $resource = SaleItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class
        ];
    }
}
