<?php

namespace App\Filament\Pages;

use Faker\Core\File;
use App\Models\ChatRoom;
use Filament\Pages\Page;
use App\Models\ChatMessage;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Webchat extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.webchat';

    protected static bool $shouldRegisterNavigation = false;
}
