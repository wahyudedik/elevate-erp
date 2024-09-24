<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'branches';

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'phone',
        'email',
        'description',
        'latitude',
        'longitude',
        'radius',
        'status',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'branch_id');
    }

}
