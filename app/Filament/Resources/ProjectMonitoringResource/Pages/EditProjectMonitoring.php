<?php

namespace App\Filament\Resources\ProjectMonitoringResource\Pages;

use App\Filament\Resources\ProjectMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectMonitoring extends EditRecord
{
    protected static string $resource = ProjectMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
