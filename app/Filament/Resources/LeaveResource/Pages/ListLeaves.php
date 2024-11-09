<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use Filament\Actions;
use App\Filament\Resources\LeaveResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LeaveResource\Widgets\AdvancedStatsOverviewWidget;

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Add New Leave')
            //     ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }

}
