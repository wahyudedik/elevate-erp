<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Recruitment extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
  
    // Nama tabel yang digunakan oleh model ini
    protected $table = 'recruitments';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'job_title',            // Judul pekerjaan
        'job_description',      // Deskripsi pekerjaan
        'employment_type',      // full_time, part_time, contract
        'location',             // Lokasi kerja
        'posted_date',          // Tanggal lowongan diposting
        'closing_date',         // Tanggal penutupan lowongan
        'status',  // Jumlah posisi yang dibutuhkan
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'posted_date' => 'date',
        'closing_date' => 'date',
    ];

    public function application()
    {
        return $this->hasMany(Applications::class);
    }
}
