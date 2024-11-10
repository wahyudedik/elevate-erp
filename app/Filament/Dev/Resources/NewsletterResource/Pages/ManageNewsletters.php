<?php

namespace App\Filament\Dev\Resources\NewsletterResource\Pages;

use App\Filament\Dev\Resources\NewsletterResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletters extends ManageRecords
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
