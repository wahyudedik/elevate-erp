<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CashFlow extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'cash_flows';

    protected $fillable = [
        'financial_report_id',
        'operating_cash_flow',
        'investing_cash_flow',
        'financing_cash_flow',
        'net_cash_flow',
    ];

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }
}
