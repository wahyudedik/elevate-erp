<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    protected $table = 'financial_reports';

    protected $fillable = [
        'report_name',
        'report_type',
        'report_period_start',
        'report_period_end',
        'total_assets',
        'total_liabilities',
        'net_income',
        'cash_flow',
    ];

}
