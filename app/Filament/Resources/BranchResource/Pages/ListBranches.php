<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use App\Filament\Resources\BranchResource\Widgets\AdvancedStatsOverviewWidget;
use App\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverviewWidget;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make()
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
