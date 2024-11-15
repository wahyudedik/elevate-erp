<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard;

class Analytics extends Page
{
    protected static ?string $cluster = Dashboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.analytics';

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('select')
                ->label('Select Widget')
                ->icon('heroicon-m-bars-3')
                ->form([
                    \Filament\Forms\Components\Select::make('widget')
                        ->options([
                            'financial' => 'Financial Overview Widget',
                            'sales' => 'Sales Performance Widget',
                            'customer' => 'Customer Insights Widget',
                            'hr' => 'HR Dashboard Widget',
                            'inventory' => 'Inventory Overview Widget',
                            'project' => 'Project Tracking Widget',
                            'supplier' => 'Supplier Performance Widget',
                        ])
                        ->default($this->selectedWidget)
                ])
                ->action(function (array $data) {
                    $this->selectedWidget = $data['widget'];
                    session(['selected_widget' => $data['widget']]);
                    $this->dispatch('refresh-widgets');
                }),
        ];
    }


    protected function getHeaderWidgets(): array
    {
        if (empty($this->selectedWidget)) {
            $this->selectedWidget = session('selected_widget', '');
        }

        if (empty($this->selectedWidget)) {
            return [];
        }

        return match ($this->selectedWidget) {
            'financial' => [
                \App\Filament\Widgets\FinancialStatsOverview::class,
            ],
            'sales' => [
                \App\Filament\Widgets\SalesStatsOverview::class,
            ],
            'customer' => [
                \App\Filament\Widgets\CustomerStatsOverview::class,
            ],
            'hr' => [
                \App\Filament\Widgets\HrStatsOverview::class,
            ],
            'inventory' => [
                \App\Filament\Widgets\InventoryLineChart::class,
                \App\Filament\Widgets\InventoryFulfillmentChart::class,
            ],
            'project' => [
                \App\Filament\Widgets\ProjectStatsOverview::class,
            ],
            'supplier' => [
                \App\Filament\Widgets\SupplierRadarChart::class,
                // ... other supplier widgets
            ],
            default => [],
        };
    }

    protected function getFooterWidgets(): array
    {
        if (empty($this->selectedWidget)) {
            $this->selectedWidget = session('selected_widget', '');
        }

        if (empty($this->selectedWidget)) {
            return [];
        }

        return match ($this->selectedWidget) {
            'financial' => [
                \App\Filament\Widgets\FinancialPieChart::class,
                \App\Filament\Widgets\FinancialLineChart::class
            ],
            'sales' => [
                \App\Filament\Widgets\SalesBarChart::class,
                \App\Filament\Widgets\SalesDoughnutChart::class,
                \App\Filament\Widgets\SalesTableWidget::class,
            ],
            'customer' => [
                \App\Filament\Widgets\CustomerLineChart::class,
                \App\Filament\Widgets\CustomerDoughnutChart::class,
            ],
            'hr' => [
                \App\Filament\Widgets\HrBarChart::class,
                \App\Filament\Widgets\HrDoughnutChart::class,
            ],
            'inventory' => [
                \App\Filament\Widgets\Inventory::class,
                \App\Filament\Widgets\TopInventoryItemsTableWidget::class,
            ],
            'project' => [
                \App\Filament\Widgets\ProjectBarChart::class,
                \App\Filament\Widgets\ProjectPieChart::class,
                \App\Filament\Widgets\ProjectTableWidget::class,
            ],
            'supplier' => [
                \App\Filament\Widgets\SupplierTableWidget::class,
                \App\Filament\Widgets\SupplierPendingPaymentTableWidget::class, 
            ],
            default => [],
        };
    }

    public ?string $selectedWidget = '';
}
