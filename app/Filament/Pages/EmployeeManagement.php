<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ManagementSDM\Employee;
use App\Filament\Widgets\EmployeeWidget;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeChartWidget;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeStatsOverviewWidget;
use App\Filament\Resources\EmployeePositionResource\Widgets\EmployeePositionChartWidget;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeWidget as WidgetsEmployeeWidget;
use App\Filament\Resources\EmployeePositionResource\Widgets\EmployeePositionStatsOverviewWidget;

class EmployeeManagement extends Page
{
    protected static ?string $navigationIcon = 'clarity-employee-group-line';

    protected static string $view = 'filament.pages.employee-management';

    protected static ?string $title = 'Employee Management';

    protected static ?string $navigationGroup = 'Management SDM';

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeStatsOverviewWidget::class,
            EmployeePositionStatsOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            EmployeeChartWidget::class,
            EmployeePositionChartWidget::class
        ];
    }
}
