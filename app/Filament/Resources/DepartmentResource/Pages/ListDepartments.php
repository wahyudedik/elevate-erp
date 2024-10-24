<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\DepartmentResource\Widgets\AdvancedStatsOverviewWidget;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
