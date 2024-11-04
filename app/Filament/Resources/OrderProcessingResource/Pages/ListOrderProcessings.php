<?php

namespace App\Filament\Resources\OrderProcessingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OrderProcessingResource;
use App\Filament\Resources\OrderProcessingResource\Widgets\AdvancedStatsOverviewWidget;

class ListOrderProcessings extends ListRecords
{
    protected static string $resource = OrderProcessingResource::class;

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
