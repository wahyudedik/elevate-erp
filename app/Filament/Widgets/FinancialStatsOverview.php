<?php

namespace App\Filament\Widgets;

use App\Models\ManagementFinancial\Accounting;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\ManagementFinancial\IncomeStatement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FinancialStatsOverview extends BaseWidget
{
    
    protected function getStats(): array
    {
        $latestIncomeStatement = IncomeStatement::latest()->first();
        
        $totalRevenue = $latestIncomeStatement?->total_revenue ?? 0;
        $profitMargin = $latestIncomeStatement ? 
            ($latestIncomeStatement->net_income / $latestIncomeStatement->total_revenue) * 100 : 0;
            
        return [
            Stat::make('Total Revenue', number_format($totalRevenue, 2))
                ->description('Total pendapatan perusahaan')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            Stat::make('Profit Margin', number_format($profitMargin, 2) . '%')
                ->description('Persentase laba bersih')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->chart([4, 5, 3, 7, 4, 5, 2, 6])
                ->color('info'),
        ];
    }
}
