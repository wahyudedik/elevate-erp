<?php

namespace App\Models\ManagementProject;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementProject\ProjectTask;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model 
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'projects';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'status',  // planned, in_progress, completed, on_hold, canceled
        'client_id',  // ID dari klien yang memesan proyek ini
        'manager_id',  // ID dari karyawan yang mengelola proyek ini
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'status' => 'string',  // planned, in_progress, completed, on_hold, canceled
        'client_id' => 'integer',  // ID dari klien yang memesan proyek ini
        'manager_id' => 'integer',  // ID dari karyawan yang mengelola proyek ini
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi dengan tabel clients
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    // Relasi dengan tabel employees (manager)
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // Relasi dengan tabel tasks (tugas-tugas dalam proyek)
    public function projectTask()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function projectResource()
    {
        return $this->hasMany(ProjectResource::class, 'project_id');
    }

    public function projectMilestone()
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id');
    }

    public function projectMonitoring():HasMany
    {
        return $this->hasMany(ProjectMonitoring::class);
    }
}
