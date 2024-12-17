<?php

namespace App\Models\ManagementSDM;

use App\Models\BaseModel;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Applications extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // Nama tabel yang digunakan oleh model ini 
    protected $table = 'applications';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'recruitment_id',
        'candidate_id',
        'status',  // applied, interviewing, offered, hired, rejected
        'resume',  // File path untuk resume/CV kandidat
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'recruitment_id',
                'candidate_id',
                'status',  // applied, interviewing, offered, hired, rejected
                'resume',  // File path untuk resume/CV kandidat
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'recruitment_id' => 'integer',
        'candidate_id' => 'integer',
        'deleted_at' => 'datetime',
        'status' => 'string',
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

    // Relasi dengan tabel recruitments
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id');
    }
}
