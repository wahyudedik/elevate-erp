<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\CashFlowResource\Widgets\CashFlowStatWidget;
use App\Filament\Resources\CashFlowResource\Widgets\CashFlowChartWidget;
use App\Filament\Resources\FinancialReportResource\Widgets\FinancialReport;
use App\Filament\Resources\BalanceSheetResource\Widgets\BalanceSheetStatWidget;
use App\Filament\Resources\BalanceSheetResource\Widgets\BalanceSheetChartWidget;
use App\Filament\Resources\IncomeStatementResource\Widgets\IncomeStatementStatWidget;
use App\Filament\Resources\FinancialReportResource\Widgets\FinancialReportChartWidget;
use App\Filament\Resources\IncomeStatementResource\Widgets\IncomeStatementChartWidget;

class FinancialReporting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?string $navigationParentItem = null;

    protected static string $view = 'filament.pages.financial-reporting';

    protected function getHeaderWidgets(): array
        {
            return [
                FinancialReport::class,
                BalanceSheetStatWidget::class,
                IncomeStatementStatWidget::class,
                CashFlowStatWidget::class,
            ];
        }
    
        protected function getFooterWidgets(): array
        {
            return [
                FinancialReportChartWidget::class,
                BalanceSheetChartWidget::class,
                IncomeStatementChartWidget::class,
                CashFlowChartWidget::class,
            ];
        }
    
}
