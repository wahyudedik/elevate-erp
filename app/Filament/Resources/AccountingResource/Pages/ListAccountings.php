<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AccountingResource;
use App\Filament\Resources\AccountingResource\Widgets\AdvancedStatsOverviewWidget;

class ListAccountings extends ListRecords
{
    protected static string $resource = AccountingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
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
