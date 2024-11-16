<?php

namespace App\Models\ManagementFinancial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class CashFlow extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'cash_flows';

    protected $fillable = [
        'company_id',
        'branch_id',
        'financial_report_id',
        'operating_cash_flow',
        'investing_cash_flow',
        'financing_cash_flow',
        'net_cash_flow',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'financial_report_id',
                'operating_cash_flow',
                'investing_cash_flow',
                'financing_cash_flow',
                'net_cash_flow',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'financial_report_id' => 'integer',
        'operating_cash_flow' => 'decimal:2',
        'investing_cash_flow' => 'decimal:2',
        'financing_cash_flow' => 'decimal:2',
        'net_cash_flow' => 'decimal:2',
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
