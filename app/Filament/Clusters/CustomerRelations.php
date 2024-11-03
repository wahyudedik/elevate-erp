<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class CustomerRelations extends Cluster
{
    protected static ?string $navigationLabel = 'Customer Relations';

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?int $navigationSort = 16; 

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
