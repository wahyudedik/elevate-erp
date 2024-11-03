<?php

namespace App\Filament\Resources\CustomerSupportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CustomerSupportResource;
use App\Filament\Resources\CustomerSupportResource\Widgets\AdvancedStatsOverviewWidget;

class ListCustomerSupports extends ListRecords
{
    protected static string $resource = CustomerSupportResource::class;

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
