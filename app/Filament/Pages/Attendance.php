<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceChartWidget;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceLineChartWidget;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceStatsOverviewWidget;

class Attendance extends Page
{
    protected static ?string $navigationIcon = 'iconpark-checkin-o';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static string $view = 'filament.pages.attendance';

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceStatsOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AttendanceChartWidget::class,
            AttendanceLineChartWidget::class,
        ];
    }
}
