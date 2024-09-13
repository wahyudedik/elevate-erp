<?php

namespace App\Filament\Resources\ProjectMonitoringResource\Pages;

use App\Filament\Resources\ProjectMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectMonitorings extends ListRecords
{
    protected static string $resource = ProjectMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
