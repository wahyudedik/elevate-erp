<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class EmployeePosition extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini 
    protected $table = 'employee_positions';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'employee_id',
        'position',
        'start_date',
        'end_date', 
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
