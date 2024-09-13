<?php

namespace App\Models\ManagementStock;

use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'supplier_name',
        'supplier_code',
        'contact_name',
        'email',
        'phone',
        'fax',
        'website',
        'tax_identification_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'status',
        'credit_limit',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function supplierTransactions():HasMany
    {
        return $this->hasMany(SupplierTransactions::class);
    }

    public function purchaseTransactions():HasMany
    {
        return $this->hasMany(PurchaseTransaction::class);
    }

    public function inventories():HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function procurement():HasMany
    {
        return $this->hasMany(Procurement::class);
    }
}
