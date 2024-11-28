<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Position extends BaseModel
{
    use HasFactory,  SoftDeletes, Notifiable, LogsActivity;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'positions';

    protected $fillable = [
        'name',
        'description',
        'company_id',
        'branch_id',
        'department_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly([
            'name',
            'description',
            'company_id',
            'branch_id',
            'department_id',
        ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'department_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

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
