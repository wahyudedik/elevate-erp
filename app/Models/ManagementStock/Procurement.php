<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Procurement extends Model
{ 
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'procurements';

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'procurement_date',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'supplier_id' => 'integer',
        'procurement_date' => 'date',
        'total_cost' => 'decimal:2',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class,  'procurement_id');
    }
}
