<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Applications extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini 
    protected $table = 'applications';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'recruitment_id',
        'candidate_id',
        'status',  // applied, interviewing, offered, hired, rejected
        'resume',  // File path untuk resume/CV kandidat
    ];

    // Relasi dengan tabel candidates
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Relasi dengan tabel recruitments
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }
}
