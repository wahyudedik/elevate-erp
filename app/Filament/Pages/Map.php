<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Map extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Management SDM';

    protected static ?string $navigationParentItem = 'Attendance';

    protected static ?string $navigationLabel = 'Maps';

    protected static string $view = 'filament.pages.map';
}
