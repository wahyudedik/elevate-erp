<?php

namespace App\Models\ManagementStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
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
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTrackings()
    {
        return $this->hasMany(InventoryTracking::class);
    }
}
