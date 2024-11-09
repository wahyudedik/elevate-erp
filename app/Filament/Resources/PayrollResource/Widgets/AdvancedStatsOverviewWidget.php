<?php

namespace App\Filament\Resources\PayrollResource\Widgets;

use App\Models\ManagementSDM\Payroll;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Payroll', Payroll::count())->icon('heroicon-o-currency-dollar')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total payroll records')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Pending', Payroll::where('payment_status', 'pending')->count())->icon('heroicon-o-clock')
                ->description('Pending payments')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Net Salary', number_format(Payroll::sum('net_salary'), 2))->icon('heroicon-o-banknotes')
                ->description("Total net salary amount")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
        ];
    }
}
