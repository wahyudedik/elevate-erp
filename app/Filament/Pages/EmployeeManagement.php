<?php

namespace App\Filament\Pages;

use App\Filament\Resources\EmployeePositionResource\Widgets\EmployeePositionChartWidget;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeChartWidget;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeWidget as WidgetsEmployeeWidget;
use App\Filament\Widgets\EmployeeWidget;
use Filament\Pages\Page;
use App\Models\ManagementSDM\Employee;

class EmployeeManagement extends Page
{
    protected static ?string $navigationIcon = 'clarity-employee-group-line';

    protected static string $view = 'filament.pages.employee-management';

    protected static ?string $title = 'Employee Management';

    protected static ?string $navigationGroup = 'Management SDM';

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeChartWidget::class,
            EmployeePositionChartWidget::class
        ];
    }

    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\LatestEmployees::class,
    //     ];
    // }

}
