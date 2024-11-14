<?php

namespace App\Filament\Widgets;

use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Attendance;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class HrStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Employees', Employee::count())
                ->icon('heroicon-o-users'),
            Stat::make('Present Today', Attendance::whereDate('date', today())
                ->where('status', 'present')->count())
                ->icon('heroicon-o-check-circle'),
            Stat::make('On Leave', Leave::whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->where('status', 'approved')
                ->count())
                ->icon('heroicon-o-calendar'),
        ];
    }
}
