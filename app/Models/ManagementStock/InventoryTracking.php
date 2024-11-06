<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTracking extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'inventory_trackings';

    protected $fillable = [
        'company_id',
        'branch_id',
        'inventory_id',
        'quantity_before',
        'quantity_after',
        'transaction_type',
        'remarks',
        'transaction_date',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'inventory_id' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'transaction_type' => 'string',
        'remarks' => 'string',
        'transaction_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
