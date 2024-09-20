<?php

namespace App\Filament\Resources\EmployeePositionResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Employee;

class EmployeePositionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Employee Position Chart';

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
        $query = $this->getFilteredQuery($activeFilter);

        $positions = $query->select('position')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('position')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $positions->pluck('count')->toArray(),
                    'backgroundColor' => $this->getChartColors(),
                ],
            ],
            'labels' => $positions->pluck('position')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    public static function getIcons(): array
    {
        return [
            'manager' => 'heroicon-o-briefcase',
            'supervisor' => 'heroicon-o-clipboard-document-list',
            'team_lead' => 'heroicon-o-user-group',
            'developer' => 'heroicon-o-code-bracket',
            'designer' => 'heroicon-o-paint-brush',
            'analyst' => 'heroicon-o-chart-bar',
            'hr_specialist' => 'heroicon-o-users',
            'accountant' => 'heroicon-o-calculator',
            'sales_representative' => 'heroicon-o-currency-dollar',
            'customer_support' => 'heroicon-o-phone',
            'marketing_specialist' => 'heroicon-o-megaphone',
            'project_manager' => 'heroicon-o-clipboard-document-check',
            'quality_assurance' => 'heroicon-o-shield-check',
            'other' => 'heroicon-o-puzzle-piece',
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }

    private function getFilteredQuery($activeFilter): \Illuminate\Database\Eloquent\Builder
    {
        $query = Employee::query();

        if ($activeFilter) {
            $query->where(function ($q) use ($activeFilter) {
                switch ($activeFilter) {
                    case 'today':
                        $q->whereDate('created_at', today());
                        break;
                    case 'week':
                        $q->whereBetween('created_at', [now()->subWeek(), now()]);
                        break;
                    case 'month':
                        $q->whereBetween('created_at', [now()->subMonth(), now()]);
                        break;
                    case 'year':
                        $q->whereYear('created_at', now()->year);
                        break;
                }
            });
        }

        return $query;
    }

    private function getChartColors(): array
    {
        return [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
        ];
    }
}
