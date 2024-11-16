<?php

namespace App\Models\ManagementStock;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class SupplierTransactions extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $table = 'supplier_transactions';

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'transaction_code',
        'transaction_type',
        'amount',
        'transaction_date',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'supplier_id',
                'transaction_code',
                'transaction_type',
                'amount',
                'transaction_date',
                'notes',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'supplier_id' => 'integer',
        'transaction_code' => 'string',
        'transaction_type' => 'string',
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'notes' => 'string',
    ];

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
