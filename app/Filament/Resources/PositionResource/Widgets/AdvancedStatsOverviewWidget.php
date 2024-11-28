<?php

namespace App\Filament\Resources\PositionResource\Widgets;

use App\Models\Company;
use App\Models\Position;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Jabatan', $this->formatNumber(Position::count()))->icon('heroicon-o-briefcase')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total posisi dalam sistem')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Jabatan Berdasarkan Perusahaan', $this->formatNumber(Company::has('positions')->count()))->icon('heroicon-o-building-office')
                ->description('Jabatan berdasarkan perusahaan')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Jabatan Berdasarkan Departemen', $this->formatNumber(Position::whereNotNull('department_id')->count()))->icon('heroicon-o-building-office-2')
                ->description("Posisi yang terkait dengan departemen")
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
