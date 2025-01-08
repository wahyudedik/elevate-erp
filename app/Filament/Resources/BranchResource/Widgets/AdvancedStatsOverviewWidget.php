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
            Stat::make('Total Cabang', $this->formatNumber(Branch::count()))->icon('heroicon-o-building-office-2')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Jumlah Seluruh Cabang')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Cabang Beroperasi', $this->formatNumber(Branch::where('status', 'active')->count()))->icon('heroicon-o-check-badge')
                ->description('Sedang Beroperasi')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->chartColor('success')
                ->iconColor('success'),
            Stat::make('Cabang Tutup', $this->formatNumber(Branch::where('status', 'inactive')->count()))->icon('heroicon-o-x-circle')
                ->description('Tidak Beroperasi')
                ->descriptionIcon('heroicon-m-arrow-trending-down', 'before')
                ->descriptionColor('danger')
                ->chartColor('danger')
                ->iconColor('danger'),
            Stat::make('Jangkauan Rata-rata', $this->formatNumber(Branch::avg('radius')) . ' meter')->icon('heroicon-o-map')
                ->description("Radius Jangkauan Cabang")
                ->descriptionIcon('heroicon-m-arrows-pointing-out', 'before')
                ->descriptionColor('primary')
                ->chartColor('primary')
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
