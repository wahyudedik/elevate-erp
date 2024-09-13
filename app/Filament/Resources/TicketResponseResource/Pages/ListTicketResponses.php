<?php

namespace App\Filament\Resources\TicketResponseResource\Pages;

use App\Filament\Resources\TicketResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketResponses extends ListRecords
{
    protected static string $resource = TicketResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
