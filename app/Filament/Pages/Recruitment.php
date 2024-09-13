<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ApplicationsResource\Widgets\ApplicationSubmitWidget;
use App\Filament\Resources\CandidateInterviewResource\Widgets\CandidateInterviewChartLineWidget;
use App\Filament\Resources\CandidateResource\Widgets\CandidateCountChartLineWidget;
use App\Filament\Resources\RecruitmentResource\Widgets\RecruitmentCountChartWidget;
use Filament\Pages\Page;

class Recruitment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.recruitment';

    protected static ?string $title = 'Recruitment Management';

    protected static ?string $navigationGroup = 'Management SDM';

    protected function getHeaderWidgets(): array
    {
        return [
            RecruitmentCountChartWidget::class,
            ApplicationSubmitWidget::class,
            CandidateCountChartLineWidget::class,
            CandidateInterviewChartLineWidget::class,
        ];
    }

    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\LatestEmployees::class,
    //     ];
    // }
}
