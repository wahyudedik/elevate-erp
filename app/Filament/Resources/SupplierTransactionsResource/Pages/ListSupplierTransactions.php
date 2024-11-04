<?php

namespace App\Filament\Resources\SupplierTransactionsResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SupplierTransactionsResource;
use App\Filament\Resources\SupplierTransactionsResource\Widgets\AdvancedStatsOverviewWidget;

class ListSupplierTransactions extends ListRecords
{
    protected static string $resource = SupplierTransactionsResource::class;

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
