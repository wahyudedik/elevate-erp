<?php

namespace App\Filament\Clusters;

use App\Models\ManagementCRM\CustomerSupport as ManagementCRMCustomerSupport;
use Filament\Clusters\Cluster;

class CustomerSupport extends Cluster
{
    protected static ?string $navigationLabel = 'Customer Support';

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?int $navigationSort = 20; //22

    protected static ?string $navigationIcon = 'hugeicons-customer-service';
}
