<?php

namespace App\Models\ManagementProject;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ProjectTask extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_tasks'; 

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',
        'task_name',
        'task_description',
        'assigned_to',  // ID karyawan yang ditugaskan
        'due_date',
        'status',  // not_started, in_progress, completed, on_hold, canceled
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'project_id' => 'integer',
        'task_name' => 'string',
        'task_description' => 'string',
        'assigned_to' => 'integer',  // ID karyawan yang ditugaskan
        'due_date' => 'date',
        'status' => 'string',  // not_started, in_progress, completed, on_hold, canceled
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi dengan tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Relasi dengan tabel employees (assigned employee)
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
