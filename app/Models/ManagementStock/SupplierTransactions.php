<?php

namespace App\Models\ManagementStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierTransactions extends Model
{
    use HasFactory;

    protected $table = 'supplier_transactions';

    protected $fillable = [
        'supplier_id',
        'transaction_code',
        'transaction_type',
        'amount',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
