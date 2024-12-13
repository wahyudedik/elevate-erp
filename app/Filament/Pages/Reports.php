<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Reports extends Page
{
    use HasPageShield;
    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan';

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dashboard',
            'Laporan'
        ];
    }


    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reports';
}
