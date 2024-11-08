<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ScheduleResource;
use App\Filament\Resources\ScheduleResource\Widgets\AdvancedStatsOverviewWidget;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('present-check')
                ->icon('heroicon-o-check-circle')
                ->url(route('present-check'))
                ->color('success'),
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
