<?php

namespace App\Filament\Resources\ProjectMilestoneResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProjectMilestoneResource;
use App\Filament\Resources\ProjectMilestoneResource\Widgets\AdvancedStatsOverviewWidget;

class ListProjectMilestones extends ListRecords
{
    protected static string $resource = ProjectMilestoneResource::class;

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
