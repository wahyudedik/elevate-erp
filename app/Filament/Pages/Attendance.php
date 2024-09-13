<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AttendanceResource\Widgets\AttendanceChartWidget;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceLineChartWidget;
use Filament\Pages\Page;

class Attendance extends Page
{
    protected static ?string $navigationIcon = 'iconpark-checkin-o';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static string $view = 'filament.pages.attendance';

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceChartWidget::class,
            AttendanceLineChartWidget::class,
        ];
    }

    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\LatestEmployees::class,
    //     ];
    // }
}
