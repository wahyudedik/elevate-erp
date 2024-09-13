<?php

namespace App\Filament\Resources\CustomerSupportResource\Pages;

use App\Filament\Resources\CustomerSupportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerSupports extends ListRecords
{
    protected static string $resource = CustomerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
