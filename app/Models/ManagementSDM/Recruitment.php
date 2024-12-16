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


class Recruitment extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'recruitments';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'job_title',            // Judul pekerjaan
        'job_description',      // Deskripsi pekerjaan
        'employment_type',      // full_time, part_time, contract
        'location',             // Lokasi kerja
        'posted_date',          // Tanggal lowongan diposting
        'closing_date',         // Tanggal penutupan lowongan
        'status',  // Jumlah posisi yang dibutuhkan
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'job_title',            // Judul pekerjaan
                'job_description',      // Deskripsi pekerjaan
                'employment_type',      // full_time, part_time, contract
                'location',             // Lokasi kerja
                'posted_date',          // Tanggal lowongan diposting
                'closing_date',         // Tanggal penutupan lowongan
                'status',  // Jumlah posisi yang dibutuhkan
            ]);
    }

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'posted_date' => 'date',
        'closing_date' => 'date',
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'employment_type' => 'string',
        'status' => 'string',
        'deleted_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan model Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function application()
    {
        return $this->hasMany(Applications::class, 'recruitment_id');
    }
}
