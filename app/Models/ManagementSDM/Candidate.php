<?php

namespace App\Models\ManagementSDM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidate extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'candidates';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'first_name',
        'last_name', 
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'national_id_number',  // Nomor KTP/Paspor
        'position_applied',  // Posisi yang dilamar
        'status',  // applied, interviewing, offered, hired, rejected
        'recruiter_id',  // ID dari recruiter yang menangani
        'application_date',
        'resume',  // Resume/CV kandidat
        'address',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'date_of_birth' => 'date',
        'application_date' => 'date',
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'recruiter_id' => 'integer',
        'deleted_at' => 'datetime',
        'gender' => 'string',
        'status' => 'string',    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($candidate) {
            $candidate->status = $candidate->status ?? 'applied';
            $candidate->application_date = $candidate->application_date ?? now();
        });

        static::created(function ($candidate) {
            $application = new Applications();
            $application->candidate_id = $candidate->id;
            $application->recruitment_id = $candidate->position_applied;
            $application->status = $candidate->status;
            $application->resume = $candidate->resume;
            $application->save();
        });
    }

    // Relasi dengan tabel Employee (recruiter)
    public function recruiter()
    {
        return $this->belongsTo(Employee::class, 'recruiter_id');
    }

    // Relasi dengan tabel candidate_interviews
    public function interviews()
    {
        return $this->hasMany(CandidateInterview::class, 'candidate_id');
    }

    public function Application()
    {
        return $this->hasMany(Applications::class, 'candidate_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
