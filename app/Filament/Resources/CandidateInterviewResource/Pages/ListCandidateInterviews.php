<?php

namespace App\Filament\Resources\CandidateInterviewResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CandidateInterviewResource;
use App\Filament\Resources\CandidateInterviewResource\Widgets\AdvancedStatsOverviewWidget;

class ListCandidateInterviews extends ListRecords
{
    protected static string $resource = CandidateInterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
