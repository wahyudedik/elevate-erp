<?php

namespace App\Models\ManagementCRM;

use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementProject\Project;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\OrderProcessing;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class Customer extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'customers';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
        'status',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'status' => 'string',
    ];

    // Relasi dengan tabel orders
    // public function orders()
    // {
    //     return $this->hasMany(Order::class);
    // }

    // // Relasi dengan tabel invoices
    // public function invoices()
    // {
    //     return $this->hasMany(Invoice::class);
    // }

    // // Relasi dengan tabel interactions (CRM)
    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function sale(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customerSupport(): HasMany
    {
        return $this->hasMany(CustomerSupport::class);
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function salesTransaction(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function orderProccessing(): HasMany
    {
        return $this->hasMany(OrderProcessing::class);
    }
}
