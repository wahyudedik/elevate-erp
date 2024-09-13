<?php

namespace App\Models\ManagementSDM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'payrolls';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'allowances',
        'deductions',  // Tunjangan
        'net_salary',  // Potongan
        'payment_date',  // Gaji bersih setelah tunjangan dan potongan
        'payment_status',  // paid, pending, failed
        'payment_method',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
