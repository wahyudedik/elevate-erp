<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard;

class Analytics extends Page
{
    protected static ?string $cluster = Dashboard::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.analytics';
} 