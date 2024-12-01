<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class FinancialReporting extends Cluster
{
    protected static ?string $navigationLabel = 'Arus Kas';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
}
