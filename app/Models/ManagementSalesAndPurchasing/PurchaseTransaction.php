<?php

namespace App\Models\ManagementSalesAndPurchasing;

use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementStock\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $table = 'purchase_transactions';

    protected $fillable = [
        'supplier_id',
        'transaction_date',
        'total_amount',
        'status',
        'purchasing_agent_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'transaction_date' => 'date'
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchasingAgent()
    {
        return $this->belongsTo(Employee::class, 'purchasing_agent_id');
    }
}
