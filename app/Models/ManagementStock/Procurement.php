<?php

namespace App\Models\ManagementStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{ 
    use HasFactory;

    protected $table = 'procurements';

    protected $fillable = [
        'supplier_id',
        'procurement_date',
        'total_cost',
        'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}
