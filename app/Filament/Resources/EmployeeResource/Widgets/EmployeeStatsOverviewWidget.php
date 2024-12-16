<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\ManagementSDM\Employee;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class EmployeeStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Karyawan', $this->formatNumber(Employee::count()))
                ->icon('heroicon-o-users')
                ->iconBackgroundColor('success')
                ->chartColor('success')
                ->iconPosition('end')
                ->description('Jumlah total karyawan')
                ->descriptionIcon('heroicon-o-user-group', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Karyawan Aktif', $this->formatNumber(Employee::where('status', 'active')->count()))
                ->icon('heroicon-o-user-circle')
                ->description('Jumlah karyawan aktif')
                ->descriptionIcon('heroicon-o-check-circle', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Departemen', $this->formatNumber(Employee::distinct('department_id')->count()))
                ->icon('heroicon-o-building-office')
                ->description("Jumlah total departemen")
                ->descriptionIcon('heroicon-o-briefcase', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Rata-rata Gaji', function () {
                $averageSalary = Employee::avg('salary');
                return $this->formatNumber($averageSalary);
            })
                ->icon('heroicon-o-currency-dollar')
                ->description("Rata-rata gaji karyawan")
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
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
