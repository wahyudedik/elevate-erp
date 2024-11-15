<?php

namespace App\Models;

use App\Models\ManagementCRM\Sale;
use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Shift;
use App\Models\Scopes\CompanyScope;
use App\Models\ManagementSDM\Payroll;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\SaleItem;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementCRM\OrderItem;
use App\Models\ManagementSDM\Candidate;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementSDM\Attendance;
use App\Models\ManagementStock\Supplier;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementProject\Project;
use App\Models\ManagementSDM\Recruitment;
use App\Models\ManagementStock\Inventory;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementSDM\Applications;
use App\Models\ManagementStock\Procurement;
use App\Models\ManagementCRM\TicketResponse;
use App\Models\ManagementFinancial\CashFlow;
use App\Models\ManagementStock\PurchaseItem;
use App\Models\ManagementCRM\CustomerSupport;
use App\Models\ManagementCRM\OrderProcessing;
use App\Models\ManagementProject\ProjectTask;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementSDM\EmployeePosition;
use App\Models\ManagementFinancial\Transaction;
use App\Models\ManagementStock\ProcurementItem;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\JournalEntry;
use App\Models\ManagementSDM\CandidateInterview;
use App\Models\ManagementCRM\CustomerInteraction;
use App\Models\ManagementProject\ProjectResource;
use App\Models\ManagementStock\InventoryTracking;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use App\Models\ManagementProject\ProjectMonitoring;
use App\Models\ManagementStock\PurchaseTransaction;
use App\Models\ManagementStock\SupplierTransactions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Branch extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
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
            ]);
    }


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

    public function applications()
    {
        return $this->hasMany(Applications::class, 'baranch_id');
    }

    public function recruitments()
    {
        return $this->hasMany(Recruitment::class, 'branch_id');
    }

    public function candidateInterviews()
    {
        return $this->hasMany(CandidateInterview::class, 'branch_id');
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'branch_id');
    }

    public function payroll()
    {
        return $this->hasMany(Payroll::class, 'branch_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'branch_id');
    }


    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class, 'branch_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class,  'branch_id');
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class, 'branch_id');
    }

    public function inventoryTracking()
    {
        return $this->hasMany(InventoryTracking::class, 'branch_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'branch_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'branch_id');
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class, 'branch_id');
    }

    public function supplierTransactions()
    {
        return $this->hasMany(SupplierTransactions::class, 'branch_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'branch_id');
    }

    public function orderProcessing()
    {
        return $this->hasMany(OrderProcessing::class, 'branch_id');
    }

    public function supplier()
    {
        return $this->hasMany(Supplier::class, 'branch_id');
    }

    public function projectMonitoring()
    {
        return $this->hasMany(ProjectMonitoring::class, 'branch_id');
    }

    public function projectResource()
    {
        return $this->hasMany(ProjectResource::class, 'branch_id');
    }

    public function branch()
    {
        return $this->hasMany(Branch::class, 'branch_id');
    }

    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class, 'branch_id');
    }

    public function project()
    {
        return $this->hasMany(Project::class, 'branch_id');
    }

    public function ticketResponses()
    {
        return $this->hasMany(TicketResponse::class, 'branch_id');
    }

    public function customerSupport()
    {
        return $this->hasMany(CustomerSupport::class, 'branch_id');
    }

    public function saleItem()
    {
        return $this->hasMany(SaleItem::class, 'branch_id');
    }

    public function sale()
    {
        return $this->hasMany(Sale::class, 'branch_id');
    }

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
