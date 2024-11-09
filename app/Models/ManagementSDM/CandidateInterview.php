<?php

namespace App\Models\ManagementSDM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CandidateInterview extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'candidate_interviews';

    // Atribut yang dapat diisi secara massal 
    protected $fillable = [
        'company_id',
        'branch_id',
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
        'interview_type' => 'string',
        'result' => 'string',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel companies
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi dengan tabel candidates
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
