<?php

namespace App\Models\ManagementSDM;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePosition extends Model
{
    use HasFactory, Notifiable, SoftDeletes; 

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    // Nama tabel yang digunakan oleh model ini 
    protected $table = 'employee_positions';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
