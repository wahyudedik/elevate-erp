<?php

namespace App\Models\ManagementFinancial;

use App\Models\User;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accounting extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'accounts';
    protected $fillable = [
        'user_id',
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

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
    
}
