<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Procurement extends Cluster
{
    protected static ?string $navigationLabel = 'Procurement';

    protected static ?string $navigationGroup = 'Management Stock';  

    protected static ?int $navigationSort = 26; //29

    protected static ?string $navigationIcon = 'polaris-package-fulfilled-icon';
}
