<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class FinancialReporting extends Cluster
{
    protected static ?string $navigationLabel = 'Financial Reporting';

    protected static ?string $navigationGroup = 'Management Financial';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
