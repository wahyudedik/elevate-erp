<?php

namespace App\Models\ManagementCRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CustomerSupport extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'customer_supports';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'customer_id',
        'subject',
        'description',
        'status',  // open, in_progress, resolved, closed
        'priority',  // low, medium, high, urgent
    ];

    // Relasi dengan tabel customers
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticketResponse()
    {
        return $this->hasMany(TicketResponse::class, 'ticket_id');
    }
}
