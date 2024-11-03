<?php

namespace App\Filament\Resources\CustomerSupportResource\Widgets;

use App\Models\ManagementCRM\CustomerSupport;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', $this->formatNumber(CustomerSupport::count()))
                ->icon('heroicon-o-ticket')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total support tickets')
                ->descriptionIcon('heroicon-o-ticket', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Open Tickets', $this->formatNumber(CustomerSupport::where('status', 'open')->count()))
                ->icon('heroicon-o-exclamation-circle')
                ->description('Tickets waiting for response')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('High Priority', $this->formatNumber(CustomerSupport::where('priority', 'high')->count()))
                ->icon('heroicon-o-bell-alert')
                ->description('High priority tickets')
                ->descriptionIcon('heroicon-o-exclamation-triangle', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Resolved', $this->formatNumber(CustomerSupport::whereIn('status', ['resolved', 'closed'])->count()))
                ->icon('heroicon-o-check-circle')
                ->description('Resolved tickets')
                ->descriptionIcon('heroicon-o-check', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
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
