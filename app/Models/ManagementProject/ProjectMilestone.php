<?php

namespace App\Models\ManagementProject;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_milestones';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'project_id',
        'milestone_name',
        'milestone_description',
        'milestone_date',
        'status',  // planned, in_progress, completed, on_hold, delayed
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'milestone_date' => 'datetime',
    ];

    // Relasi dengan tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
