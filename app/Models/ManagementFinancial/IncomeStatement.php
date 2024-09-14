<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class IncomeStatement extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'income_statements';

    protected $fillable = [
        'financial_report_id',
        'total_revenue',
        'total_expenses',
        'net_income',
    ];

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }
}
