<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Overview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationParentItem = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.overview';
} 
