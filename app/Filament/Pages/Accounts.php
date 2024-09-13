<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AccountingResource\Widgets\AccountingResourceCountChartWidget;
use App\Filament\Resources\AccountingResource\Widgets\AdvancedStatsOverviewWidget;
use App\Filament\Resources\JournalEntryResource\Widgets\AdvancedStatsOverviewWidget as WidgetsAdvancedStatsOverviewWidget;
use App\Filament\Resources\JournalEntryResource\Widgets\JournalEntryResourceCountChartWidget;
use Filament\Pages\Page;

class Accounts extends Page
{
    protected static ?string $navigationIcon = 'mdi-finance';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $label = 'Accounts';

    protected static ?string $navigationParentItem = null;

    protected static string $view = 'filament.pages.accounts';

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
            WidgetsAdvancedStatsOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AccountingResourceCountChartWidget::class,
            JournalEntryResourceCountChartWidget::class,
        ];
    }
}
