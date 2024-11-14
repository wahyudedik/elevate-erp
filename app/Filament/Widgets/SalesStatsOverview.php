<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\ManagementCRM\Sale;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SalesStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonth = Carbon::now()->month;
        $monthlyTarget = 100000000; // Set your monthly target here     

        // Calculate current month's total sales
        $monthlySales = Sale::whereMonth('sale_date', $currentMonth)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Calculate progress percentage
        $progressPercentage = min(($monthlySales / $monthlyTarget) * 100, 100);

        return [
            Stat::make('Monthly Sales Target 100M', number_format($monthlySales, 2))
                ->description(number_format($progressPercentage, 1) . '% of monthly target')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([
                    $progressPercentage,
                    100 - $progressPercentage
                ])
                ->color($progressPercentage >= 100 ? 'success' : ($progressPercentage >= 70 ? 'warning' : 'danger'))
        ];
    }
}
