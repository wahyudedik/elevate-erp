<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'sales'; 

    // Atribut yang dapat diisi secara massal 
    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sale_date',
        'total_amount',
        'status',  // pending, completed, canceled
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'customer_id' => 'integer',
        'sale_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => 'string',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

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
