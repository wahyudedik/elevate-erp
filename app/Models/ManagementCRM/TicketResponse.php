<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class TicketResponse extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'ticket_responses';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'ticket_id',  // ID tiket dukungan yang direspons
        'response',          // ID karyawan yang memberikan respons
        'employee_id',        // Isi dari respons
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'ticket_id',  // ID tiket dukungan yang direspons
                'response',          // ID karyawan yang memberikan respons
                'employee_id',        // Isi dari respons
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'ticket_id' => 'integer',
        'employee_id' => 'integer',
        'response' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel customer_supports
    public function customerSupport()
    {
        return $this->belongsTo(CustomerSupport::class, 'ticket_id');
    }

    // Relasi dengan tabel employees
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
