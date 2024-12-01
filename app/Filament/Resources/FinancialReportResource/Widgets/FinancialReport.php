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
            Stat::make('Total Laporan', FinancialReporting::count())
                ->icon('heroicon-o-document-text')
                ->description('Total laporan keuangan')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Neraca', FinancialReporting::where('report_type', 'balance_sheet')->count())
                ->icon('heroicon-o-scale')
                ->description('Laporan neraca')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Laba Rugi', FinancialReporting::where('report_type', 'income_statement')->count())
                ->icon('heroicon-o-currency-dollar')
                ->description('Laporan laba rugi')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Arus Kas', FinancialReporting::where('report_type', 'cash_flow')->count())
                ->icon('heroicon-o-banknotes')
                ->description('Laporan arus kas')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('info')
                ->iconColor('info'),
        ];
    }
}
