<?php

namespace App\Models;

use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Shift;
use App\Models\Scopes\CompanyScope;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementFinancial\CashFlow;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementFinancial\Transaction;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\JournalEntry;
use App\Models\ManagementCRM\CustomerInteraction;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }
    
    protected $table = 'branches';

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'phone',
        'email',
        'description',
        'latitude',
        'longitude',
        'radius',
        'status',
    ];


    protected $casts = [
        'company_id' => 'integer',
        'name' => 'string',
        'address' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'description' => 'string',
        'latitude' => 'double',
        'longitude' => 'double',
        'radius' => 'integer',
        'status' => 'string',
    ];

    public function customerInteractions()
    {
        return $this->hasMany(CustomerInteraction::class, 'branch_id');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'branch_id');
    }

    public function leave()
    {
        return $this->hasMany(Leave::class, 'branch_id');
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'user_id');
    }

    public function shift()
    {
        return $this->hasMany(Shift::class, 'branch_id');
    }

    public function incomeStatement()
    {
        return $this->hasMany(IncomeStatement::class, 'branch_id');
    }

    public function cashFlow()
    {
        return $this->hasMany(CashFlow::class, 'branch_id');
    }

    public function financialReports()
    {
        return $this->hasMany(FinancialReport::class, 'branch_id');
    }

    public function balanceSheets()
    {
        return $this->hasMany(BalanceSheet::class, 'branch_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'branch_id');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'branch_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'branch_id');
    }

    public function accounts()
    {
        return $this->hasMany(Accounting::class, 'branch_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'branch_id');
    }
}
