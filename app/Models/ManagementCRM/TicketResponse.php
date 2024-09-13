<?php

namespace App\Models\ManagementCRM;

use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'ticket_responses';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'ticket_id',  // ID tiket dukungan yang direspons
        'response',          // ID karyawan yang memberikan respons
        'employee_id',        // Isi dari respons
    ];

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
