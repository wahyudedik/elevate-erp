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


class FinancialReport extends BaseModel
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $table = 'financial_reports';

    protected $fillable = [
        'company_id',
        'branch_id',
        'report_name',
        'report_type', //'balance_sheet', 'income_statement', 'cash_flow'
        'report_period_start',
        'report_period_end',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'report_name',
                'report_type', //'balance_sheet', 'income_statement', 'cash_flow'
                'report_period_start',
                'report_period_end',
                'notes',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'report_name' => 'string',
        'report_type' => 'string', //'balance_sheet', 'income_statement', 'cash_flow'
        'report_period_start' =>  'date',
        'report_period_end' =>  'date',
        'notes' => 'string',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function balanceSheet()
    {
        return $this->hasMany(BalanceSheet::class, 'financial_report_id');
    }

    public function incomeStatement()
    {
        return $this->hasMany(IncomeStatement::class, 'financial_report_id');
    }

    public function cashFlow()
    {
        return $this->hasMany(related: CashFlow::class, foreignKey: 'financial_report_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
