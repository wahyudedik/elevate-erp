<?php

namespace App\Models\ManagementSDM;

use App\Models\BaseModel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Shift extends BaseModel
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    protected $table = 'shifts';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'start_time',
        'end_time',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'name',
                'start_time',
                'end_time',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'shift_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
