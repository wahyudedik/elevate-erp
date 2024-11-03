<?php

namespace App\Filament\Clusters;

use App\Models\ManagementProject\Project;
use Filament\Clusters\Cluster;

class ProjectPlanning extends Cluster
{
    protected static ?string $navigationLabel = 'Project Planning';

    protected static ?string $navigationGroup = 'Management Project';

    protected static ?int $navigationSort = 22; //26

    protected static ?string $navigationBadgeTooltip = 'Total Project';
    
    public static function getNavigationBadge(): ?string
    {
        return Project::count();
    }
    protected static ?string $navigationIcon = 'lineawesome-project-diagram-solid';
}
