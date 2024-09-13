<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\LedgerResource\Widgets\LedgerChartWidget;
use App\Filament\Resources\LedgerResource\Widgets\AdvancedStatsOverviewWidget;
use App\Filament\Resources\TransactionResource\Widgets\TransactionChartWidget;
use App\Filament\Resources\TransactionResource\Widgets\AdvancedStatsOverviewWidget as transactions;

class BookKeeping extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.book-keeping';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationLabel = 'Book Keeping';

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
            transactions::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LedgerChartWidget::class,
            TransactionChartWidget::class,
        ];
    }
}
