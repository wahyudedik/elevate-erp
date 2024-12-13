<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Employee;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Map extends Page
{
    use HasPageShield;
    protected static ?string $cluster = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 7; //29

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?string $navigationLabel = 'Maps';

    protected static string $view = 'filament.pages.map';
}
