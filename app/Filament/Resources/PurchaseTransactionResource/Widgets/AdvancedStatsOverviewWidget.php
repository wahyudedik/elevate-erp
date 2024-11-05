<?php

namespace App\Filament\Resources\PurchaseTransactionResource\Widgets;

use App\Models\ManagementStock\PurchaseTransaction;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Amount', $this->getTotalAmount())
                ->icon('heroicon-o-currency-dollar')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total purchase transactions')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Pending Transactions', $this->getPendingTransactions())
                ->icon('heroicon-o-clock')
                ->description('Awaiting receipt')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Received Transactions', $this->getReceivedTransactions())
                ->icon('heroicon-o-check-circle')
                ->description("Successfully received")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Cancelled Transactions', $this->getCancelledTransactions())
                ->icon('heroicon-o-x-circle')
                ->description("Cancelled orders")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }

    protected function getTotalAmount(): int
    {
        return PurchaseTransaction::query()->sum('total_amount');
    }

    protected function getPendingTransactions(): int
    {
        return PurchaseTransaction::query()->where('status', 'pending')->count();
    }

    protected function getReceivedTransactions(): int
    {
        return PurchaseTransaction::query()->where('status', 'received')->count();
    }

    protected function getCancelledTransactions(): int
    {
        return PurchaseTransaction::query()->where('status', 'cancelled')->count();
    }
}
