<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Webchat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.webchat';
    
    protected static bool $shouldRegisterNavigation = false;
}
