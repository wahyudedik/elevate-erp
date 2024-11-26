<?php

namespace App\Filament\Resources\BranchResource\Widgets;

use App\Models\Branch;
use App\HasTenantScope;
use Illuminate\Support\Facades\Auth;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{    

    // use HasTenantScope;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Branches', $this->formatNumber(Branch::count()))->icon('heroicon-o-building-storefront')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('All branches')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Active Branches', $this->formatNumber(Branch::where('status', 'active')->count()))->icon('heroicon-o-building-storefront')
                ->description('Active branches')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Inactive Branches', $this->formatNumber(Branch::where('status', 'inactive')->count()))->icon('heroicon-o-building-storefront')
                ->description('Inactive branches')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Average Radius', $this->formatNumber(Branch::avg('radius')) . ' m')->icon('heroicon-o-map-pin')
                ->description("Average coverage radius")
                ->descriptionIcon('heroicon-o-arrow-path', 'before')
                ->descriptionColor('primary')
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
