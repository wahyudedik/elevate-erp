<?php

namespace App\Filament\Resources\CandidateInterviewResource\Pages;

use App\Filament\Resources\CandidateInterviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidateInterview extends EditRecord
{
    protected static string $resource = CandidateInterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
