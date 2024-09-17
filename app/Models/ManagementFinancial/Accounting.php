<?php

namespace App\Models\ManagementFinancial;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accounting extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'accounts';
    protected $fillable = [
        'company_id',
        'account_name',  
        'account_number', 
        'account_type', //asset, liability, equity, revenue, expense
        'initial_balance', 
        'current_balance'
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function ledger()
    {
        return $this->hasMany(Ledger::class, 'account_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
}
