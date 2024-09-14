<?php

namespace App\Filament\Resources\IncomeStatementResource\Widgets;

use App\Models\ManagementFinancial\IncomeStatement;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class IncomeStatementStatWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
 
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', function () {
                return '' . number_format(IncomeStatement::sum('total_revenue'), 2);
            })
                ->icon('heroicon-o-currency-dollar')
                ->iconColor('success')
                ->description('Total revenue from all income statements')
                ->descriptionIcon('heroicon-o-presentation-chart-line', 'before')
                ->descriptioncolor('success'),

            Stat::make('Total Expenses', function () {
                return '' . number_format(IncomeStatement::sum('total_expenses'), 2);
            })
                ->icon('heroicon-o-banknotes')
                ->iconColor('danger')
                ->description('Total expenses from all income statements')
                ->descriptionIcon('heroicon-o-presentation-chart-line', 'before')
                ->descriptioncolor('danger'),

            Stat::make('Net Income', function () {
                return '' . number_format(IncomeStatement::sum('net_income'), 2);
            })
                ->icon('heroicon-o-scale')
                ->iconColor('primary')
                ->description('Total net income from all income statements')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptioncolor('primary'),
        ];
    }
}
