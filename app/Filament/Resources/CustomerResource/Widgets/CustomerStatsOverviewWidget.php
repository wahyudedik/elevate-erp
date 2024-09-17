<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\ManagementCRM\Customer;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class CustomerStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count())
                ->icon('heroicon-o-users')
                // ->backgroundColor('info')
                // ->progress(69)
                // ->progressBarColor('success')
                // ->iconBackgroundColor('success')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total registered customers')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Active Customers', Customer::where('status', 'active')->count())
                ->icon('heroicon-o-user-circle')
                ->description('Customers with active status')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Inactive Customers', Customer::where('status', 'inactive')->count())
                ->icon('heroicon-o-user-minus')
                ->description("Customers with inactive status")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
