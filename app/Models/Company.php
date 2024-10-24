<?php

namespace App\Models;

use App\Filament\Pages\Accounts;
use Spatie\Permission\Models\Role;
use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Shift;
use App\Models\ManagementSDM\Payroll;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementSDM\Attendance;
use Spatie\Permission\Models\Permission;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementFinancial\CashFlow;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementSDM\EmployeePosition;
use App\Models\ManagementFinancial\Transaction;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\JournalEntry;
use App\Models\ManagementCRM\CustomerInteraction;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
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

    public function customerInteractions()
    {
        return $this->hasMany(CustomerInteraction::class, 'company_id');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'company_id');
    }

    public function leave()
    {
        return $this->hasMany(Leave::class, 'company_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'company_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'company_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class, 'company_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'company_id');
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'company_id');
    }

    public function shift()
    {
        return $this->hasMany(Shift::class, 'company_id');
    }

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

    public function balanceSheet()
    {
        return $this->hasMany(BalanceSheet::class, 'company_id');
    }

    public function cashFlow()
    {
        return $this->hasMany(CashFlow::class, 'company_id');
    }

    public function financialReport()
    {
        return $this->hasMany(FinancialReport::class, 'company_id');
    }

    public function incomeStatement()
    {
        return $this->hasMany(IncomeStatement::class, 'company_id');
    }

    public function employeePosition()
    {
        return $this->hasMany(EmployeePosition::class, 'company_id');
    }

    //relasi dengan tabel payroll
    public function payroll()
    {
        return $this->hasMany(Payroll::class, 'company_id');
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Active Company';
    }

    public function role()
    {
        return $this->hasMany(Role::class, 'company_id');
    }
}
