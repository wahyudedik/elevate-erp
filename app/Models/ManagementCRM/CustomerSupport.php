<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerSupport extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'customer_supports';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id', 
        'subject',
        'description',
        'status',  // open, in_progress, resolved, closed
        'priority',  // low, medium, high, urgent
    ];

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'customer_id' => 'integer',
        'subject' => 'string',
        'description' => 'string',
        'status' => 'string',
        'priority' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi dengan tabel customers
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function ticketResponse()
    {
        return $this->hasMany(TicketResponse::class, 'ticket_id');
    }
}
