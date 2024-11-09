<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Employee;
use Filament\Pages\Page;

class Map extends Page
{

    protected static ?string $cluster = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 7; //29

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $navigationLabel = 'Maps';

    protected static string $view = 'filament.pages.map';
}
