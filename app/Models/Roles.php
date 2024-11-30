<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Roles extends SpatieRole
{
    use HasFactory; 

    protected $table = 'roles';

    protected $fillable = [
        'company_id',
        'id',
        'name',
        'guard_name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
