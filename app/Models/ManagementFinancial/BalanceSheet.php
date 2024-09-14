<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class BalanceSheet extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'balance_sheets';

    protected $fillable = [
        'financial_report_id',
        'total_assets', 
        'total_liabilities',
        'total_equity',
    ];

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }
}
