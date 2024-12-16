<?php

namespace App\Filament\Resources\EmployeePositionResource\Widgets;

use App\Models\ManagementSDM\EmployeePosition;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class EmployeePositionStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Jabatan', function () {
                return $this->formatNumber(EmployeePosition::count());
            })->icon('heroicon-o-briefcase')
                ->iconBackgroundColor('primary')
                ->chartColor('primary')
                ->iconPosition('end')
                ->description('Total jabatan karyawan')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Jabatan Aktif', function () {
                return $this->formatNumber(EmployeePosition::whereNull('end_date')->count());
            })->icon('heroicon-o-user-group')
                ->iconBackgroundColor('success')
                ->chartColor('success')
                ->iconPosition('end')
                ->description('Jabatan yang sedang aktif')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Jabatan Berakhir', function () {
                return $this->formatNumber(EmployeePosition::whereNotNull('end_date')->count());
            })->icon('heroicon-o-archive-box-x-mark')
                ->iconBackgroundColor('danger')
                ->chartColor('danger')
                ->iconPosition('end')
                ->description('Jabatan yang telah berakhir')
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
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
