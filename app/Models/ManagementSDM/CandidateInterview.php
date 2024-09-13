<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CandidateInterview extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'candidate_interviews';

    // Atribut yang dapat diisi secara massal 
    protected $fillable = [
        'candidate_id',
        'interview_date',
        'interviewer',
        'interview_type',  // phone, video, in_person
        'interview_notes',
        'result',  // passed, failed, pending 
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'interview_date' => 'date',
    ];

    // Relasi dengan tabel candidates
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
 