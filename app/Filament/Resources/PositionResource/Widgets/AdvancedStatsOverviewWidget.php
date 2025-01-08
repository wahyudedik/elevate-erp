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
            Stat::make('Total Jabatan', $this->formatNumber(Position::count()))
                ->icon('heroicon-o-briefcase')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total posisi dalam sistem')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary')
                ->extraAttributes([
                    'class' => 'ring-2 ring-primary-50 dark:ring-primary-900 hover:shadow-lg transition-all duration-300 rounded-xl'
                ]),

            Stat::make('Jabatan Berdasarkan Perusahaan', $this->formatNumber(Company::has('positions')->count()))
                ->icon('heroicon-o-building-office')
                ->chartColor('warning')
                ->description('Jabatan berdasarkan perusahaan')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
                ->extraAttributes([
                    'class' => 'ring-2 ring-warning-50 dark:ring-warning-900 hover:shadow-lg transition-all duration-300 rounded-xl'
                ]),

            Stat::make('Jabatan Berdasarkan Departemen', $this->formatNumber(Position::whereNotNull('department_id')->count()))
                ->icon('heroicon-o-building-office-2')
                ->chartColor('info')
                ->description("Posisi yang terkait dengan departemen")
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('info')
                ->iconColor('info')
                ->extraAttributes([
                    'class' => 'ring-2 ring-info-50 dark:ring-info-900 hover:shadow-lg transition-all duration-300 rounded-xl'
                ])
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
