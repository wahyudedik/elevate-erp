<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;

class Supplier extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'company_id',
        'branch_id',
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
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'supplier_name' => 'string',
        'supplier_code' => 'string',
        'contact_name' => 'string', 
        'email' => 'string',
        'phone' => 'string',
        'fax' => 'string',
        'website' => 'string',
        'tax_identification_number' => 'string',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'postal_code' => 'string',
        'country' => 'string',
        'status' => 'string',
        'credit_limit' => 'decimal:2',
    ];

    // protected $attributes = [
    //     'status' => 'active',
    // ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supplierTransactions():HasMany
    {
        return $this->hasMany(SupplierTransactions::class);
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
