<?php

namespace App\Filament\Resources\TicketResponseResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TicketResponseResource;
use App\Filament\Resources\TicketResponseResource\Widgets\AdvancedStatsOverviewWidget;

class ListTicketResponses extends ListRecords
{
    protected static string $resource = TicketResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
