<?php

namespace App\Models\ManagementSDM;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Payroll extends Model
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'payrolls';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'employee_id',
        'basic_salary',
        'allowances',
        'deductions',  // Tunjangan
        'net_salary',  // Potongan
        'payment_date',  // Gaji bersih setelah tunjangan dan potongan
        'payment_status',  // paid, pending, failed
        'payment_method',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'employee_id',
                'basic_salary',
                'allowances',
                'deductions',  // Tunjangan
                'net_salary',  // Potongan
                'payment_date',  // Gaji bersih setelah tunjangan dan potongan
                'payment_status',  // paid, pending, failed
                'payment_method',
            ]);
    }

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi dengan tabel companies
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
