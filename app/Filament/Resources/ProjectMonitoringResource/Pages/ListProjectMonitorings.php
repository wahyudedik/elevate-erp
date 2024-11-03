<?php

namespace App\Filament\Resources\ProjectMonitoringResource\Pages;

use Filament\Actions;
use Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProjectMonitoringResource;
use App\Filament\Resources\ProjectMonitoringResource\Widgets\AdvancedStatsOverviewWidget;

class ListProjectMonitorings extends ListRecords
{
    protected static string $resource = ProjectMonitoringResource::class;

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
