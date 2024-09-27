<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Position extends Model
{
    use HasFactory,  SoftDeletes, Notifiable;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'positions';

    protected $fillable = [
        'name',
        'description',
        'company_id',
        'branch_id',
        'department_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
