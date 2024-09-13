<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Attendance extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    // Nama tabel yang digunakan oleh model ini
    protected $table = 'attendances';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',  // present, absent, leave, etc.
        'note',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    // Relasi dengan tabel employees 
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
