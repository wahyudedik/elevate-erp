<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    // Nama tabel yang digunakan oleh model ini

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'attendances';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'employee_id',
        'schedule_id',
        'date',
        'schedules_check_in',
        'schedules_check_out',
        'schedules_latitude',
        'schedules_longitude',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'status',
        'note',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

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

    
}
