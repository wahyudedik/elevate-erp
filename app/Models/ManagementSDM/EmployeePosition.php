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


class EmployeePosition extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // Nama tabel yang digunakan oleh model ini 
    protected $table = 'employee_positions';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'employee_id',
        'department',
        'position',
        'start_date',
        'end_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'employee_id',
                'department',
                'position',
                'start_date',
                'end_date',
            ]);
    }

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'employee_id' => 'integer',
        'department' => 'array',
        'position' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relasi dengan tabel branches
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
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
}
