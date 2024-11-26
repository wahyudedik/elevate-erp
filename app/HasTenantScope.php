<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTenantScope
{
    public function __construct()
    {
        $this->tenantId = Auth::user()->company_id;
    }

    protected function getTenantId()
    {
        return $this->tenantId;
    }
}
