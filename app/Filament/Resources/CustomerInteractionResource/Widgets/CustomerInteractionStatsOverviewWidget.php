<?php

namespace App\Filament\Resources\CustomerInteractionResource\Widgets;

use App\Models\ManagementCRM\CustomerInteraction;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class CustomerInteractionStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Interactions', CustomerInteraction::count())
                ->icon('heroicon-o-user-group')
                // ->backgroundColor('info')
                // ->progress(69)
                // ->progressBarColor('success')
                // ->iconBackgroundColor('success')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total customer interactions')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Interactions This Month', CustomerInteraction::whereMonth('interaction_date', now()->month)->count())
                ->icon('heroicon-o-calendar')
                ->description('Interactions in the current month')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Unique Customers', CustomerInteraction::distinct('customer_id')->count())
                ->icon('heroicon-o-users')
                ->description("Number of unique customers interacted")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }
}
