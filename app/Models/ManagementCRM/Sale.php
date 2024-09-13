<?php

namespace App\Models\ManagementCRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'sales';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'customer_id',
        'sale_date',
        'total_amount',
        'status',  // pending, completed, canceled
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'sale_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // // Relasi dengan tabel customers
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi dengan tabel sale_items
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    // // Relasi dengan tabel employees
    // public function employee()
    // {
    //     return $this->belongsTo(Employee::class);
    // }
}
