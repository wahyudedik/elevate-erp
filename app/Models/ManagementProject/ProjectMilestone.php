<?php

namespace App\Models\ManagementProject;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectMilestone extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_milestones';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',
        'milestone_name',
        'milestone_description',
        'milestone_date',
        'status',  // planned, in_progress, completed, on_hold, delayed
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'project_id' => 'integer',
        'milestone_name' => 'string',
        'milestone_description' => 'string',
        'milestone_date' => 'date',
        'status' => 'string',  // planned, in_progress, completed, on_hold, delayed
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
