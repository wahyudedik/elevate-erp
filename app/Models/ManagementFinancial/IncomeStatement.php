<?php

namespace App\Models\ManagementFinancial;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomeStatement extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'income_statements';

    protected $fillable = [
        'company_id',
        'financial_report_id',
        'total_revenue',
        'total_expenses',
        'net_income',
    ];

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
