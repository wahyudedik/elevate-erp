<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\CustomerResource\Widgets\CustomerChartWidget;
use App\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverviewWidget;
use App\Filament\Resources\CustomerInteractionResource\Widgets\CustomerInteractionChartWidget;
use App\Filament\Resources\CustomerInteractionResource\Widgets\CustomerInteractionStatsOverviewWidget;

class CustomerRelation extends Page
{
    protected static ?string $navigationIcon = 'carbon-customer';

    protected static ?string $navigationGroup = 'Management CRM';

    protected static string $view = 'filament.pages.customer-relation';

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerStatsOverviewWidget::class,
            CustomerInteractionStatsOverviewWidget::class
        ];
    }


    protected function getFooterWidgets(): array
    {
        return [
            CustomerChartWidget::class,
            CustomerInteractionChartWidget::class,
        ];
    }
}
