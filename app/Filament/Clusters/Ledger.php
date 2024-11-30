<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Ledger extends Cluster
{
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationGroup = 'Manajemen Keuangan';
    protected static ?string $navigationIcon = 'tabler-report-analytics';
}
