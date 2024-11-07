<?php

namespace App\Filament\Resources\EmployeePositionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\EmployeePositionResource;
use App\Filament\Resources\EmployeePositionResource\Widgets\EmployeePositionStatsOverviewWidget;

class ListEmployeePositions extends ListRecords
{
    protected static string $resource = EmployeePositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeePositionStatsOverviewWidget::class,
        ];
    }
}
