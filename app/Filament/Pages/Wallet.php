<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Wallet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.wallet';
 
    protected static bool $shouldRegisterNavigation = false;
    
}
