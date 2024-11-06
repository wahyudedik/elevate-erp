<?php

namespace App\Filament\Resources\InventoryTrackingResource\Pages;

use App\Filament\Resources\InventoryTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryTrackings extends ListRecords
{
    protected static string $resource = InventoryTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryTrackingResource\Widgets\AdvancedStatsOverviewWidget::class,
        ];
    }
}
