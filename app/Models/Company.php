<?php

namespace App\Models;

use App\Models\Branch;
use App\Filament\Pages\Accounts;
use App\Models\ManagementCRM\Sale;
use Spatie\Permission\Models\Role;
use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Shift;
use App\Models\ManagementSDM\Payroll;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\SaleItem;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementCRM\OrderItem;
use App\Models\ManagementSDM\Candidate;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use App\Models\ManagementSDM\Attendance;
use App\Models\ManagementStock\Supplier;
use Spatie\Permission\Models\Permission;
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
        'name' => 'string',
        'slug' => 'string',
        'logo' => 'string',
        'description' => 'string',
        'address' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'website' => 'string',
        'slogan' => 'string',
        'mission' => 'string',
        'vision' => 'string',
        'qna' => 'array',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class, 'company_id');
    }

    public function chatMessageRead()
    {
        return $this->hasMany(ChatMessageRead::class, 'company_id');
    }

    public function chatMessage()
    {
        return $this->hasMany(ChatMessage::class, 'company_id');
    }

    public function chatRoomUser()
    {
        return $this->hasMany(ChatRoomUser::class, 'company_id');
    }

    public function chatRoom()
    {
        return $this->hasMany(ChatRoom::class, 'company_id');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'company_id');
    }

    public function recruitments()
    {
        return $this->hasMany(Recruitment::class, 'company_id');
    }

    public function candidateInterviews()
    {
        return $this->hasMany(CandidateInterview::class, 'company_id');
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'company_id');
    }

    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class,  'company_id');
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class, 'company_id');
    }

    public function inventoryTracking()
    {
        return $this->hasMany(InventoryTracking::class, 'company_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'company_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'company_id');
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class, 'company_id');
    }

    public function supplierTransactions()
    {
        return $this->hasMany(SupplierTransactions::class, 'company_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'company_id');
    }

    public function orderProcessing()
    {
        return $this->hasMany(OrderProcessing::class, 'company_id');
    }

    public function supplier()
    {
        return $this->hasMany(Supplier::class, 'company_id');
    }

    public function projectMonitoring()
    {
        return $this->hasMany(ProjectMonitoring::class, 'company_id');
    }

    public function projectResource()
    {
        return $this->hasMany(ProjectResource::class, 'company_id');
    }

    public function branch()
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class, 'company_id');
    }

    public function project()
    {
        return $this->hasMany(Project::class, 'company_id');
    }

    public function ticketResponses()
    {
        return $this->hasMany(TicketResponse::class, 'company_id');
    }

    public function customerSupport()
    {
        return $this->hasMany(CustomerSupport::class, 'company_id');
    }

    public function saleItem()
    {
        return $this->hasMany(SaleItem::class, 'company_id');
    }

    public function sale()
    {
        return $this->hasMany(Sale::class, 'company_id');
    }

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





















































    

    

    
        
        
        
        
        
        
        
        
        
        
        
        
    


    
        
        
        
        
        
        
        
        
        
        
        
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
        
    

    
    
    
        
    

    
    
        
    

    
    
        
    

use Illuminate\Database\Eloquent\Relations\HasMany;