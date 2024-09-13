<?php

namespace App\Models\ManagementSalesAndPurchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_transaction_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($purchaseItem) {
            $purchaseItem->total_price = $purchaseItem->quantity * $purchaseItem->unit_price;
        });
    }
}
