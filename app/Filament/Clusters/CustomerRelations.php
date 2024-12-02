<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class CustomerRelations extends Cluster
{
    protected static ?string $navigationLabel = 'Customer Relations';

    protected static ?string $navigationGroup = 'Manajemen CRM';

    protected static ?int $navigationSort = 16; 

    protected static ?string $navigationIcon = 'carbon-customer';
}
