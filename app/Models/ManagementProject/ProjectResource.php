<?php

namespace App\Models\ManagementProject;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectResource extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_resources'; 

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',
        'resource_name',
        'resource_type', // human, material, financial, equipment
        'resource_cost', // Jumlah sumber daya yang dialokasikan
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'project_id' => 'integer',
        'resource_name' => 'string',
        'resource_type' => 'string', // human, material, financial, equipment
        'resource_cost' => 'decimal:2', // Jumlah sumber daya yang dialokasikan
    ];

    // Relasi dengan tabel companies
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi dengan tabel branches
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
