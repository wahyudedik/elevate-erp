<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class PurchaseTransaction extends Model
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    protected $table = 'purchase_transactions';

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'transaction_date',
        'total_amount',
        'status',
        'purchasing_agent_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'supplier_id',
                'transaction_date',
                'total_amount',
                'status',
                'purchasing_agent_id',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'supplier_id' => 'integer',
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
        'status' => 'string',
        'purchasing_agent_id' => 'integer',
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_transaction_id');
    }

    public function purchasingAgent()
    {
        return $this->belongsTo(Employee::class, 'purchasing_agent_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
