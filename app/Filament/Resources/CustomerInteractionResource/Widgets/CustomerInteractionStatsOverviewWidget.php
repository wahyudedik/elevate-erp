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
            Stat::make('Total Interactions', $this->formatNumber(CustomerInteraction::count()))
                ->icon('heroicon-o-user-group')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total customer interactions')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Interactions This Month', $this->formatNumber(CustomerInteraction::whereMonth('interaction_date', now()->month)->count()))
                ->icon('heroicon-o-calendar')
                ->description('Interactions in the current month')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Unique Customers', $this->formatNumber(CustomerInteraction::distinct('customer_id')->count()))
                ->icon('heroicon-o-users')
                ->description("Number of unique customers interacted")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }

    protected function formatNumber($number)
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return $formattedNumber . $suffixes[$suffixIndex];
    }

}
