<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Accounting extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'accounts';
    protected $fillable = [
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
    
}
