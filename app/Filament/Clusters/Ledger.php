<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Ledger extends Cluster
{
    protected static ?string $navigationLabel = 'Book Kepping';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationGroup = 'Management Financial';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
