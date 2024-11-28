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
            Stat::make('Total Cabang', $this->formatNumber(Branch::count()))->icon('heroicon-o-building-storefront')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Semua Cabang')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Cabang Aktif', $this->formatNumber(Branch::where('status', 'active')->count()))->icon('heroicon-o-building-storefront')
                ->description('Cabang Aktif')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Cabang Nonaktif', $this->formatNumber(Branch::where('status', 'inactive')->count()))->icon('heroicon-o-building-storefront')
                ->description('Cabang Nonaktif')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Rata-rata Radius', $this->formatNumber(Branch::avg('radius')) . ' m')->icon('heroicon-o-map-pin')
                ->description("Rata-rata Radius Cabang")
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
