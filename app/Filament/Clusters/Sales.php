<?php

namespace App\Filament\Clusters;

use App\Models\ManagementCRM\Sale;
use Filament\Clusters\Cluster;

class Sales extends Cluster
{
    protected static ?string $navigationLabel = 'Sale';

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?int $navigationSort = 18; //20

    protected static ?string $navigationBadgeTooltip = 'Total Sale';
    
    public static function getNavigationBadge(): ?string
    {
        return Sale::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
