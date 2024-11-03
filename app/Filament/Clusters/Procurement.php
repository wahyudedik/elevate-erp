<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Procurement extends Cluster
{
    protected static ?string $navigationLabel = 'Project Planning';

    protected static ?string $navigationGroup = 'Management Project';

    protected static ?int $navigationSort = 22; //26

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
