<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class FinancialReport extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'financial_reports';

    protected $fillable = [ 
        'report_name',
        'report_type', //'balance_sheet', 'income_statement', 'cash_flow'
        'report_period_start',
        'report_period_end',
        'notes',
    ];

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

}
