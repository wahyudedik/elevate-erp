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
            Stat::make('Total Departemen', $this->formatNumber(Department::count()))
                ->icon('heroicon-m-building-office-2')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Jumlah keseluruhan departemen dalam sistem')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary')
                ->extraAttributes([
                    'class' => 'ring-2 ring-primary-50 hover:ring-primary-100 transition-all duration-300 rounded-xl shadow-sm hover:shadow-md'
                ]),
            Stat::make('Departemen Terafiliasi', $this->formatNumber(Department::whereNotNull('branch_id')->count()))
                ->icon('heroicon-m-building-storefront')
                ->chartColor('warning')
                ->description('Departemen yang terhubung dengan cabang')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
                ->extraAttributes([
                    'class' => 'ring-2 ring-warning-50 hover:ring-warning-100 transition-all duration-300 rounded-xl shadow-sm hover:shadow-md'
                ]),
            Stat::make('Departemen Terbaru', $this->formatNumber(Department::where('created_at', '>=', now()->subDays(30))->count()))
                ->icon('heroicon-m-clock')
                ->chartColor('info')
                ->description('Ditambahkan dalam 30 hari terakhir')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->descriptionColor('info')
                ->iconColor('info')
                ->extraAttributes([
                    'class' => 'ring-2 ring-info-50 hover:ring-info-100 transition-all duration-300 rounded-xl shadow-sm hover:shadow-md'
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
