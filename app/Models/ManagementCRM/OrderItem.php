<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementCRM\OrderProcessing;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class OrderItem extends Model
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    protected $table = 'order_items';

    protected $fillable = [
        'company_id',
        'branch_id',
        'order_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'order_id',
                'product_name',
                'quantity',
                'unit_price',
                'total_price',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'order_id' => 'integer',
        'product_name' => 'string',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function orderProcessing()
    {
        return $this->belongsTo(OrderProcessing::class, 'order_id');
    }
}
