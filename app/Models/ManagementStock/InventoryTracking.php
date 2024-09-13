<?php

namespace App\Models\ManagementStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTracking extends Model
{
    use HasFactory;

    protected $table = 'inventory_trackings';

    protected $fillable = [
        'inventory_id',
        'quantity_before',
        'quantity_after',
        'transaction_type',
        'remarks',
        'transaction_date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
