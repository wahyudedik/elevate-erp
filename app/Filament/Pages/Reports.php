<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard;

class Reports extends Page
{
    // protected static ?string $cluster = Dashboard::class;

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $navigationGroup = 'Reports';
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reports';
} 
