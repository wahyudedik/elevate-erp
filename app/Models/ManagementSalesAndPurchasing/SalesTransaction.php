<?php

namespace App\Models\ManagementSalesAndPurchasing;

use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\OrderProcessing;

class SalesTransaction extends Model 
{
    use HasFactory;

    protected $fillable = [
            'customer_id',
            'transaction_date',
            'total_amount',
            'status',
            'sales_rep_id',
        ];
    
        protected $casts = [
            'transaction_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    
        public function customer()
        {
            return $this->belongsTo(Customer::class);
        }
    
        public function salesRep()
        {
            return $this->belongsTo(Employee::class, 'sales_rep_id');
        }

        public function salesItem(): HasMany
        {
            return $this->hasMany(SalesItem::class);
        }

        public function orderProccessing(): HasMany
        {
            return $this->hasMany(OrderProcessing::class);
        }
    
}
