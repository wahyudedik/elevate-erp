<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
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