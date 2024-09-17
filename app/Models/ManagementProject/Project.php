<?php

namespace App\Models\ManagementProject;

use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementProject\ProjectTask;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Project extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'projects';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

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
        return $this->hasMany(ProjectResource::class);
    }

    public function projectMilestone()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function projectMonitoring():HasMany
    {
        return $this->hasMany(ProjectMonitoring::class);
    }
}
