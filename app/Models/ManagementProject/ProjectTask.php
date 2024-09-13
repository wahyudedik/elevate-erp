<?php

namespace App\Models\ManagementProject;

use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_tasks';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'project_id',
        'task_name',
        'task_description',
        'assigned_to',  // ID karyawan yang ditugaskan
        'due_date',
        'status',  // not_started, in_progress, completed, on_hold, canceled
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'due_date' => 'datetime',
    ];

    // Relasi dengan tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi dengan tabel employees (assigned employee)
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
