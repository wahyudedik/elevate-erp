<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'schedules';

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'employee_id',
        'shift_id',
        'date',
        'is_wfa',
        'is_banned',

    ];

    protected $casts = [
        'company_id' => 'integer',
        'user_id' => 'integer',
        'branch_id' => 'integer',
        'employee_id' => 'integer',
        'shift_id' => 'integer',
        'date' => 'date',
        'is_wfa' => 'boolean',
        'is_banned' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
