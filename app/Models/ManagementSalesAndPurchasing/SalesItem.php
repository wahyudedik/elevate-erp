<?php

namespace App\Models\ManagementSalesAndPurchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_transaction_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($salesItem) {
            $salesItem->total_price = $salesItem->quantity * $salesItem->unit_price;
        });
    }
}
