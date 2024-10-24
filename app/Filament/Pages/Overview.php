<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard;

class Overview extends Page
{
    protected static ?string $cluster = Dashboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.overview';
}
