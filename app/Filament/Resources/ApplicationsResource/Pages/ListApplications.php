<?php

namespace App\Filament\Resources\ApplicationsResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ApplicationsResource;
use App\Filament\Resources\ApplicationsResource\Widgets\AdvancedStatsOverviewWidget;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return[
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
