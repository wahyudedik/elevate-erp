<?php

namespace App\Filament\Resources\CandidateInterviewResource\Pages;

use App\Filament\Resources\CandidateInterviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCandidateInterviews extends ListRecords
{
    protected static string $resource = CandidateInterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
