<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use Filament\Actions;
use App\Filament\Resources\ShiftResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ShiftResource\Widgets\AdvancedStatsOverviewWidget;

class ListShifts extends ListRecords
{
    protected static string $resource = ShiftResource::class;

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
