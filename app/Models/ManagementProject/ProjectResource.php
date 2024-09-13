<?php

namespace App\Models\ManagementProject;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'project_resources';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'project_id',
        'resource_name',
        'resource_type', // human, material, financial, equipment
        'resource_cost', // Jumlah sumber daya yang dialokasikan
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'resource_cost' => 'decimal:2',
    ];

    // Relasi dengan tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
