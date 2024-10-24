<?php

namespace App\Filament\Resources\CustomerInteractionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CustomerInteractionResource;
use App\Filament\Resources\CustomerInteractionResource\Widgets\CustomerInteractionStatsOverviewWidget;

class ListCustomerInteractions extends ListRecords
{
    protected static string $resource = CustomerInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerInteractionStatsOverviewWidget::class
        ];
    }
}
