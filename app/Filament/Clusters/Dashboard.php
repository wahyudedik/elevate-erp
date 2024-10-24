<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Dashboard extends Cluster
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
