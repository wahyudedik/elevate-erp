<?php

namespace App\Models\ManagementStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    use HasFactory;

    protected $table = 'procurement_items';

    protected $fillable = [
        'procurement_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }
}
