<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'schedules';

    protected $fillable = [
        'company_id',
        'employee_id',
        'shift_id',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

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