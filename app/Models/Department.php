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


class Department extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'departments';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'name',
                'description',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
