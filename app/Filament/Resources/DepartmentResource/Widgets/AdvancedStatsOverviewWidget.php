<?php

namespace App\Filament\Resources\DepartmentResource\Widgets;

use App\Models\Department;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
 
    protected function getStats(): array
    {
        return [
            Stat::make('Total Departments', $this->formatNumber(Department::count()))->icon('heroicon-o-building-office-2')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total number of departments')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Departments with Branches', $this->formatNumber(Department::whereNotNull('branch_id')->count()))->icon('heroicon-o-building-storefront')
                ->description('Departments associated with branches')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Recently Added', $this->formatNumber(Department::where('created_at', '>=', now()->subDays(30))->count()))->icon('heroicon-o-clock')
                ->description("Departments added in the last 30 days")
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
