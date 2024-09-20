<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Employee;

class EmployeeChartWidget extends ChartWidget
{
    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $query = Employee::query();

        if ($activeFilter) {
            $query->where(function ($q) use ($activeFilter) {
                $this->applyFilter($q, $activeFilter);
            });
        }

        $statuses = ['active', 'inactive', 'terminated', 'resigned'];
        $statusCounts = $this->getStatusCounts($query, $statuses);

        return [
            'datasets' => [
                [
                    'label' => 'Employee Status',
                    'data' => $statusCounts,
                    'backgroundColor' => [
                        '#10B981', // green for active
                        '#F59E0B', // yellow for inactive
                        '#EF4444', // red for terminated
                        '#6B7280', // gray for resigned
                    ],
                ],
            ],
            'labels' => ['Active', 'Inactive', 'Terminated', 'Resigned'],
        ];
    }

    protected function applyFilter($query, $filter): void
    {
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }
    }

    protected function getStatusCounts($query, array $statuses): array
    {
        return array_map(function ($status) use ($query) {
            return $query->where('status', $status)->count();
        }, $statuses);
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%',
        ];
    }

    public static function getWidgetIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    protected static ?string $heading = 'Employee Status Distribution';

    protected static ?int $sort = 2;
}
