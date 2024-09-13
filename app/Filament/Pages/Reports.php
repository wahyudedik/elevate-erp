<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationParentItem = 'Dashboard';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reports';
} 
