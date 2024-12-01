<?php

namespace App\Models\ManagementFinancial;

use App\Models\BaseModel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class BalanceSheet extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $table = 'balance_sheets'; 

    protected $fillable = [
        'branch_id',
        'company_id',
        'financial_report_id',
        'total_assets',
        'total_liabilities',
        'total_equity',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'branch_id',
                'company_id',
                'financial_report_id',
                'total_assets',
                'total_liabilities',
                'total_equity',
            ]);
    }

    protected $casts = [
        'branch_id' => 'integer',
        'company_id' => 'integer',
        'financial_report_id' => 'integer',
        'total_assets' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'total_equity' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
