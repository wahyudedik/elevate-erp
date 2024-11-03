<?php

namespace App\Filament\Resources\ProjectResorcessResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProjectResorcessResource;
use App\Filament\Resources\ProjectResorcessResource\Widgets\AdvancedStatsOverviewWidget;

class ListProjectResorcesses extends ListRecords
{
    protected static string $resource = ProjectResorcessResource::class;

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
