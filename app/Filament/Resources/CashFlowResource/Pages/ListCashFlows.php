<?php

namespace App\Filament\Resources\CashFlowResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CashFlowResource;
use App\Filament\Resources\CashFlowResource\Widgets\CashFlowStatWidget;

class ListCashFlows extends ListRecords
{
    protected static string $resource = CashFlowResource::class;

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
            CashFlowStatWidget::class,
        ];
    }
}
