<?php

namespace App\Filament\Resources\TicketResponseResource\Pages;

use App\Filament\Resources\TicketResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketResponse extends EditRecord
{
    protected static string $resource = TicketResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
