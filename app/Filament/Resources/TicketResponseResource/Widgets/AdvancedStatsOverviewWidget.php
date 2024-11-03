<?php

namespace App\Filament\Resources\TicketResponseResource\Widgets;

use App\Models\Company;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementCRM\TicketResponse;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Responses', $this->formatNumber(TicketResponse::count()))->icon('heroicon-o-chat-bubble-left')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total ticket responses')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Active Companies', $this->formatNumber(Company::whereHas('ticketResponses')->count()))->icon('heroicon-o-building-office')
                ->description('Companies with responses')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Employee Responses', $this->formatNumber(Employee::whereHas('ticketResponses')->count()))->icon('heroicon-o-users')
                ->description("Active responding employees")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }

    protected function formatNumber($number)
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return $formattedNumber . $suffixes[$suffixIndex];
    }

}
