<?php

namespace App\Filament\Widgets;

use App\Models\ManagementCRM\CustomerSupport;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\ManagementCRM\CustomerInteraction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class CustomerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $averageRating = CustomerSupport::where('status', 'resolved')
            ->avg('customer_rating') ?? 0;

        $totalInteractions = CustomerInteraction::count();

        $satisfactionPercentage = CustomerSupport::where('customer_satisfaction', 'satisfied')
            ->count() / CustomerSupport::count() * 100 ?? 0;

        return [
            Stat::make('Average Customer Rating', number_format($averageRating, 1) . ' / 5.0')
                ->description('Overall customer satisfaction score')
                ->descriptionIcon('heroicon-m-star')
                ->chart([3.5, 4.2, 4.5, 4.3, 4.8, $averageRating])
                ->color('success'),

            Stat::make('Customer Interactions', $totalInteractions)
                ->description('Total customer touchpoints')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([65, 78, 82, 91, 87, $totalInteractions])
                ->color('info'),

            Stat::make('Satisfaction Rate', number_format($satisfactionPercentage, 1) . '%')
                ->description('Customers reporting satisfaction')
                ->descriptionIcon('heroicon-m-heart')
                ->chart([75, 82, 85, 89, 90, $satisfactionPercentage])
                ->color('warning'),
        ];
    }
}
