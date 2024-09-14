<?php

namespace App\Filament\Resources\FinancialReportResource\Widgets;

use App\Models\ManagementFinancial\FinancialReport as FinancialReporting;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class FinancialReport extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Reports', FinancialReporting::count())
                ->icon('heroicon-o-document-text')
                ->description('Total financial reports')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Balance Sheets', FinancialReporting::where('report_type', 'balance_sheet')->count())
                ->icon('heroicon-o-scale')
                ->description('Balance sheet reports')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Income Statements', FinancialReporting::where('report_type', 'income_statement')->count())
                ->icon('heroicon-o-currency-dollar')
                ->description('Income statement reports')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Cash Flow Reports', FinancialReporting::where('report_type', 'cash_flow')->count())
                ->icon('heroicon-o-banknotes')
                ->description('Cash flow reports')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('info')
                ->iconColor('info'),
        ];
    }
}
