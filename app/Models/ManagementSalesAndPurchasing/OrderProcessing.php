<?php

namespace App\Models\ManagementSalesAndPurchasing;

use App\Models\ManagementCRM\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class OrderProcessing extends Model
{
    use HasFactory;
    protected $table = 'order_processings';
    protected $fillable = [
        'customer_id',
        'order_date',
        'total_amount',
        'status',
        'sales_transaction_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'status' => 'string',
        'sales_transaction_id' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesTransaction()  
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

}
