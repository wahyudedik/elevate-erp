<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Employee extends Cluster
{
    protected static ?string $navigationLabel = 'Karyawan';

    protected static ?string $slug = 'karyawan';

    protected static ?string $navigationGroup = 'Manajemen SDM';

    protected static ?int $navigationSort = 1; //29

    protected static ?string $navigationIcon = 'clarity-employee-group-line';
}
