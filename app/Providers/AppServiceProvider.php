<?php

namespace App\Providers;

use App\Models\User;
use App\Filament\Pages\Map;
use App\Policies\MapPolicy;
use App\Policies\ActivityPolicy;
use App\Models\ManagementCRM\Sale;
use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Shift;
use Illuminate\Support\Facades\Gate;
use App\Models\ManagementSDM\Payroll;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\SaleItem;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use Spatie\Activitylog\ActivityLogger;
use App\Models\ManagementCRM\OrderItem;
use App\Models\ManagementSDM\Candidate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use App\Models\ManagementSDM\Attendance;
use App\Models\ManagementStock\Supplier;
use App\Models\ManagementProject\Project;
use App\Models\ManagementSDM\Recruitment;
use App\Models\ManagementStock\Inventory;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementSDM\Applications;
use App\Policies\ManagementCRM\SalePolicy;
use App\Models\ManagementStock\Procurement;
use App\Policies\ManagementSDM\LeavePolicy;
use App\Policies\ManagementSDM\ShiftPolicy;
use App\Models\ManagementCRM\TicketResponse;
use App\Models\ManagementFinancial\CashFlow;
use App\Models\ManagementStock\PurchaseItem;
use App\Models\ManagementCRM\CustomerSupport;
use App\Models\ManagementCRM\OrderProcessing;
use App\Models\ManagementProject\ProjectTask;
use App\Policies\ManagementSDM\PayrollPolicy;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementSDM\EmployeePosition;
use App\Policies\ManagementCRM\CustomerPolicy;
use App\Policies\ManagementCRM\SaleItemPolicy;
use App\Policies\ManagementSDM\EmployeePolicy;
use App\Policies\ManagementSDM\SchedulePolicy;
use App\Models\ManagementFinancial\Transaction;
use App\Models\ManagementStock\ProcurementItem;
use App\Policies\ManagementCRM\OrderItemPolicy;
use App\Policies\ManagementSDM\CandidatePolicy;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\JournalEntry;
use App\Models\ManagementSDM\CandidateInterview;
use App\Policies\ManagementSDM\AttendancePolicy;
use App\Policies\ManagementStock\SupplierPolicy;
use App\Models\ManagementCRM\CustomerInteraction;
use App\Models\ManagementProject\ProjectResource;
use App\Models\ManagementStock\InventoryTracking;
use App\Policies\ManagementProject\ProjectPolicy;
use App\Policies\ManagementSDM\RecruitmentPolicy;
use App\Policies\ManagementStock\InventoryPolicy;
use App\Models\ManagementProject\ProjectMilestone;
use App\Policies\ManagementFinancial\LedgerPolicy;
use App\Policies\ManagementSDM\ApplicationsPolicy;
use App\Models\ManagementFinancial\FinancialReport;
use App\Models\ManagementFinancial\IncomeStatement;
use App\Models\ManagementProject\ProjectMonitoring;
use App\Models\ManagementStock\PurchaseTransaction;
use App\Policies\ManagementStock\ProcurementPolicy;
use App\Models\ManagementStock\SupplierTransactions;
use App\Policies\ManagementCRM\TicketResponsePolicy;
use App\Policies\ManagementFinancial\CashFlowPolicy;
use App\Policies\ManagementStock\PurchaseItemPolicy;
use App\Policies\ManagementCRM\CustomerSupportPolicy;
use App\Policies\ManagementCRM\OrderProcessingPolicy;
use App\Policies\ManagementProject\ProjectTaskPolicy;
use App\Policies\ManagementFinancial\AccountingPolicy;
use App\Policies\ManagementSDM\EmployeePositionPolicy;
use App\Policies\ManagementFinancial\TransactionPolicy;
use App\Policies\ManagementStock\ProcurementItemPolicy;
use App\Policies\ManagementFinancial\BalanceSheetPolicy;
use App\Policies\ManagementFinancial\JournalEntryPolicy;
use App\Policies\ManagementSDM\CandidateInterviewPolicy;
use App\Policies\ManagementCRM\CustomerInteractionPolicy;
use App\Policies\ManagementProject\ProjectResourcePolicy;
use App\Policies\ManagementStock\InventoryTrackingPolicy;
use App\Policies\ManagementProject\ProjectMilestonePolicy;
use Filament\Notifications\Livewire\DatabaseNotifications;
use App\Policies\ManagementFinancial\FinancialReportPolicy;
use App\Policies\ManagementFinancial\IncomeStatementPolicy;
use App\Policies\ManagementProject\ProjectMonitoringPolicy;
use App\Policies\ManagementStock\PurchaseTransactionPolicy;
use App\Policies\ManagementStock\SupplierTransactionsPolicy;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->usertype === 'dev';
        });

        // Gate::policy(Activity::class, ActivityPolicy::class);

        //Management Financial
        Gate::policy(Accounting::class, AccountingPolicy::class);
        Gate::policy(BalanceSheet::class, BalanceSheetPolicy::class);
        Gate::policy(CashFlow::class, CashFlowPolicy::class);
        Gate::policy(FinancialReport::class, FinancialReportPolicy::class);
        Gate::policy(IncomeStatement::class, IncomeStatementPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Ledger::class, LedgerPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);

        //Management CRM
        Gate::policy(CustomerInteraction::class, CustomerInteractionPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerSupport::class, CustomerSupportPolicy::class);
        Gate::policy(OrderItem::class, OrderItemPolicy::class);
        Gate::policy(OrderProcessing::class, OrderProcessingPolicy::class);
        Gate::policy(SaleItem::class, SaleItemPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(TicketResponse::class, TicketResponsePolicy::class);

        //Management Project
        Gate::policy(ProjectMilestone::class, ProjectMilestonePolicy::class);
        Gate::policy(ProjectMonitoring::class, ProjectMonitoringPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectResource::class, ProjectResourcePolicy::class);
        Gate::policy(ProjectTask::class, ProjectTaskPolicy::class);

        //Management SDM
        Gate::policy(Applications::class, ApplicationsPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(CandidateInterview::class, CandidateInterviewPolicy::class);
        Gate::policy(Candidate::class, CandidatePolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(EmployeePosition::class, EmployeePositionPolicy::class);
        Gate::policy(Leave::class, LeavePolicy::class);
        Gate::policy(Payroll::class, PayrollPolicy::class);
        Gate::policy(Recruitment::class, RecruitmentPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);

        //Management Stock
        Gate::policy(Inventory::class, InventoryPolicy::class);
        Gate::policy(InventoryTracking::class, InventoryTrackingPolicy::class);
        Gate::policy(Procurement::class, ProcurementPolicy::class);
        Gate::policy(ProcurementItem::class, ProcurementItemPolicy::class);
        Gate::policy(PurchaseItem::class, PurchaseItemPolicy::class);
        Gate::policy(PurchaseTransaction::class, PurchaseTransactionPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(SupplierTransactions::class, SupplierTransactionsPolicy::class);
    }
}
