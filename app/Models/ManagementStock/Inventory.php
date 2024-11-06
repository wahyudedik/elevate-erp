<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'inventories';

    protected $fillable = [
        'company_id',
        'branch_id',
        'item_name',
        'sku',
        'quantity',
        'purchase_price',
        'selling_price',
        'location',
        'supplier_id',
        'status',
    ];


    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'item_name' => 'string',
        'sku' =>  'string',
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'location' => 'string',
        'supplier_id' => 'integer',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function inventoryTrackings()
    {
        return $this->hasMany(InventoryTracking::class, 'inventory_id');
    }
}
