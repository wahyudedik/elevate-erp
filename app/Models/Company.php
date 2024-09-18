<?php

namespace App\Models;

use App\Filament\Pages\Accounts;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementFinancial\JournalEntry;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementFinancial\Transaction;
use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model implements HasCurrentTenantLabel
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'slogan',
        'mission',
        'vision',
        'qna',
    ];


    protected $casts = [
        'qna' => 'array',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    public function employee()
    {
        return $this->hasMany(Employee::class, 'company_id');
    }

    public function accounting()
    {
        return $this->hasMany(Accounting::class, 'company_id');
    }

    public function journalEntry()
    {
        return $this->hasMany(JournalEntry::class, 'company_id');
    }

    public function ledger()
    {
        return $this->hasMany(Ledger::class, 'company_id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'company_id');
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Active Company';
    }
    
}
