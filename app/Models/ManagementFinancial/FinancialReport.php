<?php

namespace App\Models\ManagementFinancial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialReport extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

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
