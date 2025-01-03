<?php

namespace App\Models\ManagementSDM;

use App\Models\BaseModel;
use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Carbon;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Attendance extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $table = 'attendances';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'employee_id',
        'schedule_id',
        'date',
        'schedules_check_in',
        'schedules_check_out',
        'schedules_latitude',
        'schedules_longitude',
        'check_in',
        'check_out',
        'latitude_check_in',
        'longitude_check_in',
        'latitude_check_out',
        'longitude_check_out',
        'status',
        'note',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'user_id',
                'branch_id',
                'employee_id',
                'schedule_id',
                'date',
                'schedules_check_in',
                'schedules_check_out',
                'schedules_latitude',
                'schedules_longitude',
                'check_in',
                'check_out',
                'latitude_check_in',
                'longitude_check_in',
                'latitude_check_out',
                'longitude_check_out',
                'status',
                'note',
            ]);
    }

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'date' => 'date',
        'schedules_check_in' => 'datetime:H:i',
        'schedules_check_out' => 'datetime:H:i',
        'schedules_latitude' => 'double',
        'schedules_longitude' => 'double',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'latitude_check_in' => 'double',
        'longitude_check_in' => 'double',
        'latitude_check_out' => 'double',
        'longitude_check_out' => 'double',
        'status' => 'string',
    ];

    // relasi dengan tabel branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan tabel employees 
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function isLate()
    {
        $scheduleStartTime = Carbon::parse($this->schedules_check_in);
        $startTime = Carbon::parse($this->check_in);

        if ($startTime->greaterThan($scheduleStartTime)) {
            $duration = $scheduleStartTime->diff($startTime);
            return [
                'status' => true,
                'duration' => "{$duration->h} jam {$duration->i} menit"
            ];
        }

        return ['status' => false];
    }

    public function workDuration()
    {
        $startTime = Carbon::parse($this->check_in);
        $endTime = Carbon::parse($this->check_out);

        $duration = $startTime->diff($endTime);

        $hours = $duration->h;
        $minutes = $duration->i;

        return "{$hours} jam {$minutes} menit";
    }
}
