<?php

namespace App\Filament\Resources\RecruitmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\RecruitmentResource;
use App\Filament\Resources\RecruitmentResource\Widgets\AdvancedStatsOverviewWidget;

class ListRecruitments extends ListRecords
{
    protected static string $resource = RecruitmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return[
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
