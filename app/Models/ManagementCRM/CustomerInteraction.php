<?php

namespace App\Models\ManagementCRM;

use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Notifications\Notifiable;

class CustomerInteraction extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'customer_interactions';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'customer_id',
        'interaction_type',   // call, email, meeting, note, etc.
        'interaction_date',
        'details',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'interaction_date' => 'datetime',
        'interaction_type' => 'json'
    ];

    // Relasi dengan tabel customers
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
