<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementCRM\Sale;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class OrderProcessing extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $table = 'order_processings';

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'order_date',
        'total_amount',
        'status',
        'sales_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'customer_id',
                'order_date',
                'total_amount',
                'status',
                'sales_id',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'customer_id' => 'integer',
        'order_date' => 'date',
        'total_amount' =>  'decimal:2',
        'status' => 'string',
        'sales_id' => 'integer',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function sales()
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }
}
