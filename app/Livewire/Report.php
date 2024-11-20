<?php

namespace App\Livewire;

use Livewire\Component;
use Barryvdh\DomPDF\PDF;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use App\Models\ManagementCRM\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementCRM\OrderProcessing;
use App\Models\ManagementFinancial\Accounting;
use App\Models\ManagementFinancial\JournalEntry;

class Report extends Component
{
    public $start_date;
    public $end_date;
    public $report_type;
    public $reportData = [];

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function generateReport()
    {
        //profit loss
        if ($this->report_type === 'profit_loss') {
            // Calculate Revenue
            $revenue = JournalEntry::where('entry_type', 'credit')
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'revenue');
                })
                ->whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->sum('amount');

            // Calculate Expenses
            $expenses = JournalEntry::where('entry_type', 'debit')
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'expense');
                })
                ->whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->sum('amount');

            // Calculate Net Income/Loss
            $netIncome = $revenue - $expenses;

            $this->reportData = [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net_income' => $netIncome,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        //balance sheet
        if ($this->report_type === 'balance_sheet') {
            // Calculate Assets
            $assets = Accounting::where('account_type', 'asset')
                ->with(['ledgerEntries' => function ($query) {
                    $query->whereBetween('transaction_date', [$this->start_date, $this->end_date]);
                }])
                ->get()
                ->map(function ($account) {
                    return [
                        'name' => $account->account_name,
                        'balance' => $account->current_balance
                    ];
                });

            // Calculate Liabilities
            $liabilities = Accounting::where('account_type', 'liability')
                ->with(['ledgerEntries' => function ($query) {
                    $query->whereBetween('transaction_date', [$this->start_date, $this->end_date]);
                }])
                ->get()
                ->map(function ($account) {
                    return [
                        'name' => $account->account_name,
                        'balance' => $account->current_balance
                    ];
                });

            // Calculate Equity
            $equity = Accounting::where('account_type', 'equity')
                ->with(['ledgerEntries' => function ($query) {
                    $query->whereBetween('transaction_date', [$this->start_date, $this->end_date]);
                }])
                ->get()
                ->map(function ($account) {
                    return [
                        'name' => $account->account_name,
                        'balance' => $account->current_balance
                    ];
                });

            $this->reportData = [
                'assets' => $assets,
                'total_assets' => $assets->sum('balance'),
                'liabilities' => $liabilities,
                'total_liabilities' => $liabilities->sum('balance'),
                'equity' => $equity,
                'total_equity' => $equity->sum('balance'),
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        if ($this->report_type === 'cash_flow') {
            // Calculate Operating Cash Flow
            $operatingCashFlow = JournalEntry::whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'operating');
                })
                ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE -amount END) as net_flow')
                ->first()->net_flow ?? 0;

            // Calculate Investing Cash Flow
            $investingCashFlow = JournalEntry::whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'investing');
                })
                ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE -amount END) as net_flow')
                ->first()->net_flow ?? 0;

            // Calculate Financing Cash Flow
            $financingCashFlow = JournalEntry::whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'financing');
                })
                ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE -amount END) as net_flow')
                ->first()->net_flow ?? 0;

            $netCashFlow = $operatingCashFlow + $investingCashFlow + $financingCashFlow;

            $this->reportData = [
                'operating_cash_flow' => $operatingCashFlow,
                'investing_cash_flow' => $investingCashFlow,
                'financing_cash_flow' => $financingCashFlow,
                'net_cash_flow' => $netCashFlow,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        //expense analysis
        if ($this->report_type === 'expense_analysis') {
            // Get expenses grouped by account/department
            $expenses = JournalEntry::where('entry_type', 'debit')
                ->whereHas('account', function ($query) {
                    $query->where('account_type', 'expense');
                })
                ->whereBetween('entry_date', [$this->start_date, $this->end_date])
                ->with('account')
                ->get()
                ->groupBy('account.account_name');

            // Calculate totals and percentages
            $totalExpenses = $expenses->sum(function ($group) {
                return $group->sum('amount');
            });

            $expenseAnalysis = $expenses->map(function ($group) use ($totalExpenses) {
                return [
                    'amount' => $group->sum('amount'),
                    'percentage' => ($group->sum('amount') / $totalExpenses) * 100,
                    'transactions' => $group->count()
                ];
            });

            $this->reportData = [
                'expenses' => $expenseAnalysis,
                'total_expenses' => $totalExpenses,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        //sales summary
        if ($this->report_type === 'sales_summary') {
            // Get sales data from both sales and order processing tables
            $salesData = Sale::whereBetween('sale_date', [$this->start_date, $this->end_date])
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    SUM(total_amount) as total_sales,
                    AVG(total_amount) as average_sale,
                    COUNT(DISTINCT customer_id) as unique_customers
                ')
                ->first();

            $orderData = OrderProcessing::whereBetween('order_date', [$this->start_date, $this->end_date])
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    SUM(total_amount) as total_sales,
                    AVG(total_amount) as average_sale,
                    COUNT(DISTINCT customer_id) as unique_customers
                ')
                ->first();

            // Combine data from both sources
            $this->reportData = [
                'total_transactions' => $salesData->total_transactions + $orderData->total_transactions,
                'total_sales' => $salesData->total_sales + $orderData->total_sales,
                'average_sale' => ($salesData->total_transactions + $orderData->total_transactions) > 0
                    ? ($salesData->total_sales + $orderData->total_sales) /
                    ($salesData->total_transactions + $orderData->total_transactions)
                    : 0,
                'unique_customers' => $salesData->unique_customers + $orderData->unique_customers,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        //product performance
        if ($this->report_type === 'product_performance') {
            // Combine sales from both tables
            $salesProducts = DB::table('sale_items')
                ->select(
                    'product_name',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total_price) as total_revenue'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->whereBetween('created_at', [$this->start_date, $this->end_date])
                ->groupBy('product_name');

            $orderProducts = DB::table('order_items')
                ->select(
                    'product_name',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total_price) as total_revenue'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->whereBetween('created_at', [$this->start_date, $this->end_date])
                ->groupBy('product_name');

            $products = $salesProducts->union($orderProducts)
                ->get()
                ->sortByDesc('total_revenue');

            $this->reportData = [
                'products' => $products,
                'top_products' => $products->take(5),
                'bottom_products' => $products->sortBy('total_revenue')->take(5),
                'total_revenue' => $products->sum('total_revenue'),
                'total_transactions' => $products->sum('transaction_count')
            ];
        }

        //customer sales
        if ($this->report_type === 'customer_sales') {
            $salesData = DB::table('sales')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->whereBetween('sale_date', [$this->start_date, $this->end_date])
                ->select(
                    'customers.name as customer_name',
                    DB::raw('COUNT(*) as purchase_frequency'),
                    DB::raw('SUM(total_amount) as total_spent'),
                    DB::raw('AVG(total_amount) as average_transaction')
                )
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_spent');

            $orderData = DB::table('order_processings')
                ->join('customers', 'order_processings.customer_id', '=', 'customers.id')
                ->whereBetween('order_date', [$this->start_date, $this->end_date])
                ->select(
                    'customers.name as customer_name',
                    DB::raw('COUNT(*) as purchase_frequency'),
                    DB::raw('SUM(total_amount) as total_spent'),
                    DB::raw('AVG(total_amount) as average_transaction')
                )
                ->groupBy('customers.id', 'customers.name');

            $combinedData = $salesData->union($orderData)
                ->get()
                ->sortByDesc('total_spent');

            $this->reportData = [
                'customers' => $combinedData,
                'total_revenue' => $combinedData->sum('total_spent'),
                'total_transactions' => $combinedData->sum('purchase_frequency'),
                'average_customer_value' => $combinedData->avg('total_spent')
            ];
        }

        //sales forecast
        if ($this->report_type === 'sales_forecast') {
            // Get historical sales data
            $historicalSales = DB::table('sales')
                ->select(
                    DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'),
                    DB::raw('SUM(total_amount) as monthly_sales')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get order processing data
            $orderSales = DB::table('order_processings')
                ->select(
                    DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                    DB::raw('SUM(total_amount) as monthly_sales')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Combine and calculate average monthly growth
            $monthlyData = collect();
            $previousAmount = 0;
            $growthRates = [];

            foreach ($historicalSales->concat($orderSales)->groupBy('month') as $month => $data) {
                $totalAmount = collect($data)->sum('monthly_sales');
                if ($previousAmount > 0) {
                    $growthRate = ($totalAmount - $previousAmount) / $previousAmount;
                    $growthRates[] = $growthRate;
                }
                $monthlyData->push([
                    'month' => $month,
                    'amount' => $totalAmount
                ]);
                $previousAmount = $totalAmount;
            }

            // Calculate average growth rate
            $averageGrowthRate = count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0;

            // Generate forecast for next 6 months
            $lastAmount = $monthlyData->last() ? $monthlyData->last()['amount'] : 0;
            $forecast = collect();
            $startDate = $monthlyData->last() ? Carbon::parse($monthlyData->last()['month'])->addMonth() : now();

            for ($i = 1; $i <= 6; $i++) {
                $forecastAmount = $lastAmount * (1 + $averageGrowthRate);
                $forecast->push([
                    'month' => $startDate->format('Y-m'),
                    'amount' => $forecastAmount,
                    'growth_rate' => $averageGrowthRate * 100
                ]);
                $lastAmount = $forecastAmount;
                $startDate->addMonth();
            }

            $this->reportData = [
                'historical_data' => $monthlyData,
                'forecast_data' => $forecast,
                'average_growth_rate' => $averageGrowthRate * 100
            ];
        }

        // customer retention
        if ($this->report_type === 'customer_retention') {
            // Get all customers who made purchases in previous period
            $previousPeriodStart = Carbon::parse($this->start_date)->subMonth();
            $previousPeriodEnd = Carbon::parse($this->end_date)->subMonth();

            $previousCustomers = DB::table('customers')
                ->select('customers.id')
                ->join('sales', 'customers.id', '=', 'sales.customer_id')
                ->whereBetween('sales.sale_date', [$previousPeriodStart, $previousPeriodEnd])
                ->union(
                    DB::table('customers')
                        ->select('customers.id')
                        ->join('order_processings', 'customers.id', '=', 'order_processings.customer_id')
                        ->whereBetween('order_processings.order_date', [$previousPeriodStart, $previousPeriodEnd])
                )
                ->distinct()
                ->pluck('id');

            // Get returning customers in current period
            $returningCustomers = DB::table('customers')
                ->select('customers.*')
                ->join('sales', 'customers.id', '=', 'sales.customer_id')
                ->whereBetween('sales.sale_date', [$this->start_date, $this->end_date])
                ->whereIn('customers.id', $previousCustomers)
                ->union(
                    DB::table('customers')
                        ->select('customers.*')
                        ->join('order_processings', 'customers.id', '=', 'order_processings.customer_id')
                        ->whereBetween('order_processings.order_date', [$this->start_date, $this->end_date])
                        ->whereIn('customers.id', $previousCustomers)
                )
                ->distinct()
                ->get();
            $retentionRate = $previousCustomers->count() > 0
                ? ($returningCustomers->count() / $previousCustomers->count()) * 100
                : 0;

            $this->reportData = [
                'total_previous_customers' => $previousCustomers->count(),
                'returning_customers' => $returningCustomers->count(),
                'retention_rate' => $retentionRate,
                'customer_details' => $returningCustomers
            ];
        }

        // customer feedback
        if ($this->report_type === 'customer_feedback') {
            $feedbackData = DB::table('customer_supports')
                ->select(
                    'subject',
                    'description',
                    'priority',
                    'status',
                    'created_at'
                )
                ->whereBetween('created_at', [$this->start_date, $this->end_date])
                ->get();

            $satisfactionSummary = [
                'high_priority' => $feedbackData->where('priority', 'high')->count(),
                'medium_priority' => $feedbackData->where('priority', 'medium')->count(),
                'low_priority' => $feedbackData->where('priority', 'low')->count(),
            ];

            $statusSummary = [
                'open' => $feedbackData->where('status', 'open')->count(),
                'in_progress' => $feedbackData->where('status', 'in_progress')->count(),
                'resolved' => $feedbackData->where('status', 'resolved')->count(),
                'closed' => $feedbackData->where('status', 'closed')->count()
            ];

            $this->reportData = [
                'feedback_list' => $feedbackData,
                'priority_summary' => $satisfactionSummary,
                'status_summary' => $statusSummary,
                'total_feedback' => $feedbackData->count()
            ];
        }

        // lead conversion
        if ($this->report_type === 'lead_conversion') {
            // Get customer interactions data
            $interactions = DB::table('customer_interactions')
                ->join('customers', 'customer_interactions.customer_id', '=', 'customers.id')
                ->whereBetween('customer_interactions.created_at', [$this->start_date, $this->end_date])
                ->select(
                    'customers.id',
                    'customers.name',
                    'customers.email',
                    'customer_interactions.interaction_type',
                    'customer_interactions.created_at'
                )
                ->get();

            // Calculate conversion metrics
            $totalLeads = $interactions->count();
            $convertedLeads = DB::table('sales')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->whereBetween('sales.created_at', [$this->start_date, $this->end_date])
                ->distinct('customers.id')
                ->count();

            $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

            // Get conversion stages
            $stageConversion = $interactions
                ->groupBy(function ($interaction) {
                    return json_decode($interaction->interaction_type)->stage ?? 'Unknown';
                })
                ->map(function ($group) {
                    return $group->count();
                });

            $this->reportData = [
                'total_leads' => $totalLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $conversionRate,
                'stage_conversion' => $stageConversion,
                'recent_conversions' => $interactions->take(10)
            ];
        }

        // employee attendance
        if ($this->report_type === 'employee_attendance') {
            $attendances = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->whereBetween('attendances.date', [$this->start_date, $this->end_date])
                ->select(
                    'employees.first_name',
                    'employees.last_name',
                    'employees.employee_code',
                    'attendances.date',
                    'attendances.check_in',
                    'attendances.check_out',
                    'attendances.status',
                    'attendances.note'
                )
                ->orderBy('employees.first_name')
                ->orderBy('attendances.date')
                ->get();

            $summary = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->whereBetween('attendances.date', [$this->start_date, $this->end_date])
                ->select(
                    'employees.id',
                    'employees.first_name',
                    'employees.last_name',
                    DB::raw('COUNT(*) as total_days'),
                    DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present_days'),
                    DB::raw('SUM(CASE WHEN attendances.status = "late" THEN 1 ELSE 0 END) as late_days'),
                    DB::raw('SUM(CASE WHEN attendances.status = "absent" THEN 1 ELSE 0 END) as absent_days'),
                    DB::raw('SUM(CASE WHEN attendances.status = "on_leave" THEN 1 ELSE 0 END) as leave_days')
                )
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->get();

            $this->reportData = [
                'attendances' => $attendances,
                'summary' => $summary,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];
        }

        // payroll
        if ($this->report_type === 'payroll') {
            $payrollData = DB::table('payrolls')
                ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
                ->whereBetween('payrolls.payment_date', [$this->start_date, $this->end_date])
                ->select(
                    'employees.first_name',
                    'employees.last_name',
                    'employees.employee_code',
                    'payrolls.basic_salary',
                    'payrolls.allowances',
                    'payrolls.deductions',
                    'payrolls.net_salary',
                    'payrolls.payment_date',
                    'payrolls.payment_status'
                )
                ->orderBy('employees.first_name')
                ->get();

            $summary = [
                'total_payroll' => $payrollData->sum('net_salary'),
                'total_employees' => $payrollData->count(),
                'total_basic_salary' => $payrollData->sum('basic_salary'),
                'total_allowances' => $payrollData->sum('allowances'),
                'total_deductions' => $payrollData->sum('deductions'),
                'average_salary' => $payrollData->avg('net_salary')
            ];

            $this->reportData = [
                'payroll_details' => $payrollData,
                'summary' => $summary
            ];
        }

        // employee turnover
        if ($this->report_type === 'employee_turnover') {
            // Get new hires
            $newHires = DB::table('employees')
                ->whereBetween('date_of_joining', [$this->start_date, $this->end_date])
                ->select(
                    'first_name',
                    'last_name',
                    'employee_code',
                    'date_of_joining',
                    'position_id',
                    'department_id'
                )
                ->get();

            // Get exits
            $exits = DB::table('employees')
                ->where('status', 'terminated')
                ->orWhere('status', 'resigned')
                ->whereBetween('updated_at', [$this->start_date, $this->end_date])
                ->select(
                    'first_name',
                    'last_name',
                    'employee_code',
                    'updated_at as exit_date',
                    'position_id',
                    'department_id',
                    'status as exit_type'
                )
                ->get();

            $this->reportData = [
                'new_hires' => $newHires,
                'exits' => $exits,
                'summary' => [
                    'total_new_hires' => $newHires->count(),
                    'total_exits' => $exits->count(),
                    'net_change' => $newHires->count() - $exits->count(),
                    'turnover_rate' => DB::table('employees')->count() > 0
                        ? ($exits->count() / DB::table('employees')->count()) * 100
                        : 0
                ]
            ];
        }

        // employee performance
        if ($this->report_type === 'employee_performance') {
            $employees = DB::table('employees')
                ->join('attendances', 'employees.id', '=', 'attendances.employee_id')
                ->join('payrolls', 'employees.id', '=', 'payrolls.employee_id')
                ->whereBetween('attendances.date', [$this->start_date, $this->end_date])
                ->select(
                    'employees.id',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.employee_code',
                    'employees.position_id',
                    'employees.department_id',
                    DB::raw('COUNT(CASE WHEN attendances.status = "present" THEN 1 END) as attendance_rate'),
                    DB::raw('COUNT(CASE WHEN attendances.status = "late" THEN 1 END) as late_count'),
                    DB::raw('AVG(payrolls.net_salary) as average_salary')
                )
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.employee_code', 'employees.position_id', 'employees.department_id')
                ->get();

            $this->reportData = [
                'employees' => $employees,
                'summary' => [
                    'total_employees' => $employees->count(),
                    'average_attendance' => $employees->avg('attendance_rate'),
                    'average_performance' => $employees->avg('attendance_rate') - $employees->avg('late_count'),
                ]
            ];
        }

        // stock level
        if ($this->report_type === 'stock_level') {
            $inventories = DB::table('inventories')
                ->select(
                    'item_name',
                    'sku',
                    'quantity',
                    'purchase_price',
                    'selling_price',
                    'location',
                    'status'
                )
                ->where('status', '!=', 'discontinued')
                ->orderBy('quantity', 'asc')
                ->get();

            $this->reportData = [
                'inventories' => $inventories,
                'summary' => [
                    'total_items' => $inventories->count(),
                    'out_of_stock' => $inventories->where('status', 'out_of_stock')->count(),
                    'low_stock' => $inventories->where('quantity', '<', 10)->count(),
                    'total_value' => $inventories->sum(function ($item) {
                        return $item['quantity'] * $item['purchase_price'];
                    })
                ]
            ];
        }

        // inventory valuation
        if ($this->report_type === 'inventory_valuation') {
            $inventories = DB::table('inventories')
                ->select(
                    'item_name',
                    'sku',
                    'quantity',
                    'purchase_price',
                    'selling_price',
                    'location',
                    DB::raw('quantity * purchase_price as total_value'),
                    DB::raw('quantity * selling_price as potential_value')
                )
                ->where('status', '!=', 'discontinued')
                ->orderBy('item_name')
                ->get();

            $this->reportData = [
                'inventories' => $inventories,
                'summary' => [
                    'total_items' => $inventories->count(),
                    'total_purchase_value' => $inventories->sum('total_value'),
                    'total_potential_value' => $inventories->sum('potential_value'),
                    'total_quantity' => $inventories->sum('quantity')
                ]
            ];
        }

        // stock movement
        if ($this->report_type === 'stock_movement') {
            $movements = DB::table('inventory_trackings')
                ->join('inventories', 'inventory_trackings.inventory_id', '=', 'inventories.id')
                ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
                ->select(
                    'inventories.item_name',
                    'inventories.sku',
                    'inventory_trackings.quantity_before',
                    'inventory_trackings.quantity_after',
                    'inventory_trackings.transaction_type',
                    'inventory_trackings.remarks',
                    'inventory_trackings.transaction_date'
                )
                ->orderBy('transaction_date', 'desc')
                ->get();

            $summary = [
                'total_movements' => $movements->count(),
                'total_additions' => $movements->where('transaction_type', 'addition')->count(),
                'total_deductions' => $movements->where('transaction_type', 'deduction')->count(),
                'net_change' => $movements->sum(function ($movement) {
                    return $movement['transaction_type'] === 'addition'
                        ? ($movement['quantity_after'] - $movement['quantity_before'])
                        : - ($movement['quantity_before'] - $movement['quantity_after']);
                })
            ];

            $this->reportData = [
                'movements' => $movements,
                'summary' => $summary
            ];
        }

        // fulfillment time
        if ($this->report_type === 'fulfillment_time') {
            $orders = DB::table('order_processings')
                ->select(
                    'order_processings.id',
                    'order_processings.id as order_number',
                    'order_processings.order_date',
                    'order_processings.status',
                    'customers.name as customer_name',
                    DB::raw('TIMESTAMPDIFF(HOUR, order_processings.order_date, order_processings.updated_at) as fulfillment_hours')
                )
                ->join('customers', 'order_processings.customer_id', '=', 'customers.id')
                ->whereBetween('order_date', [$this->start_date, $this->end_date])
                ->where('order_processings.status', 'delivered')
                ->get();

            $this->reportData = [
                'orders' => $orders,
                'summary' => [
                    'total_orders' => $orders->count(),
                    'average_time' => $orders->avg('fulfillment_hours'),
                    'fastest_time' => $orders->min('fulfillment_hours'),
                    'slowest_time' => $orders->max('fulfillment_hours'),
                    'orders_under_24h' => $orders->where('fulfillment_hours', '<=', 24)->count(),
                    'orders_over_48h' => $orders->where('fulfillment_hours', '>', 48)->count()
                ]
            ];
        }

        // project status
        if ($this->report_type === 'project_status') {
            $projects = DB::table('projects')
                ->leftJoin('customers', 'projects.client_id', '=', 'customers.id')
                ->leftJoin('employees', 'projects.manager_id', '=', 'employees.id')
                ->leftJoin('project_monitorings', 'projects.id', '=', 'project_monitorings.project_id')
                ->whereBetween('projects.start_date', [$this->start_date, $this->end_date])
                ->select(
                    'projects.*',
                    'customers.name as client_name',
                    'employees.first_name as manager_first_name',
                    'employees.last_name as manager_last_name',
                    'project_monitorings.completion_percentage',
                    'project_monitorings.status as monitoring_status'
                )
                ->get();

            $summary = [
                'total_projects' => $projects->count(),
                'active_projects' => $projects->where('status', 'in_progress')->count(),
                'completed_projects' => $projects->where('status', 'completed')->count(),
                'on_hold_projects' => $projects->where('status', 'on_hold')->count(),
                'cancelled_projects' => $projects->where('status', 'cancelled')->count(),
                'average_completion' => $projects->avg('completion_percentage')
            ];

            $this->reportData = [
                'projects' => $projects,
                'summary' => $summary
            ];
        }

        // milestone progress
        if ($this->report_type === 'milestone_progress') {
            $milestones = DB::table('project_milestones')
                ->join('projects', 'project_milestones.project_id', '=', 'projects.id')
                ->leftJoin('customers', 'projects.client_id', '=', 'customers.id')
                ->whereBetween('project_milestones.milestone_date', [$this->start_date, $this->end_date])
                ->select(
                    'projects.name as project_name',
                    'customers.name as client_name',
                    'project_milestones.milestone_name',
                    'project_milestones.milestone_description',
                    'project_milestones.milestone_date',
                    'project_milestones.status'
                )
                ->orderBy('project_milestones.milestone_date')
                ->get();

            $summary = [
                'total_milestones' => $milestones->count(),
                'achieved_milestones' => $milestones->where('status', 'achieved')->count(),
                'pending_milestones' => $milestones->where('status', 'pending')->count(),
                'achievement_rate' => $milestones->count() > 0
                    ? ($milestones->where('status', 'achieved')->count() / $milestones->count()) * 100
                    : 0
            ];

            $this->reportData = [
                'milestones' => $milestones,
                'summary' => $summary
            ];
        }

        // resource allocation
        if ($this->report_type === 'resource_allocation') {
            $resources = DB::table('project_resources')
                ->join('projects', 'project_resources.project_id', '=', 'projects.id')
                ->whereBetween('project_resources.created_at', [$this->start_date, $this->end_date])
                ->select(
                    'projects.name as project_name',
                    'project_resources.resource_name',
                    'project_resources.resource_type',
                    'project_resources.resource_cost',
                    DB::raw('COUNT(*) as allocation_count')
                )
                ->groupBy(
                    'projects.name',
                    'project_resources.resource_name',
                    'project_resources.resource_type',
                    'project_resources.resource_cost'
                )
                ->get();

            $summary = [
                'total_resources' => $resources->count(),
                'total_cost' => $resources->sum('resource_cost'),
                'human_resources' => $resources->where('resource_type', 'human')->count(),
                'material_resources' => $resources->where('resource_type', 'material')->count(),
                'financial_resources' => $resources->where('resource_type', 'financial')->count()
            ];

            $this->reportData = [
                'resources' => $resources,
                'summary' => $summary
            ];
        }

        // budget utilization
        if ($this->report_type === 'budget_utilization') {
            $projects = DB::table('projects')
                ->leftJoin('customers', 'projects.client_id', '=', 'customers.id')
                ->leftJoin('employees', 'projects.manager_id', '=', 'employees.id')
                ->leftJoin('project_monitorings', 'projects.id', '=', 'project_monitorings.project_id')
                ->whereBetween('projects.start_date', [$this->start_date, $this->end_date])
                ->select(
                    'projects.name as project_name',
                    'projects.budget as planned_budget',
                    'customers.name as client_name',
                    'employees.first_name as manager_first_name',
                    'employees.last_name as manager_last_name',
                    'project_monitorings.actual_cost',
                    'project_monitorings.completion_percentage',
                    DB::raw('(project_monitorings.actual_cost / projects.budget) * 100 as budget_utilization_percentage')
                )
                ->get();

            $this->reportData = [
                'projects' => $projects,
                'summary' => [
                    'total_projects' => $projects->count(),
                    'total_budget' => $projects->sum('planned_budget'),
                    'total_spent' => $projects->sum('actual_cost'),
                    'average_utilization' => $projects->avg('budget_utilization_percentage')
                ]
            ];
        }

        // supplier performance
        if ($this->report_type === 'supplier_performance') {
            $suppliers = DB::table('suppliers')
                ->leftJoin('purchase_transactions', 'suppliers.id', '=', 'purchase_transactions.supplier_id')
                ->leftJoin('procurements', 'suppliers.id', '=', 'procurements.supplier_id')
                ->whereBetween('purchase_transactions.transaction_date', [$this->start_date, $this->end_date])
                ->select(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    DB::raw('COUNT(purchase_transactions.id) as total_transactions'),
                    DB::raw('COUNT(CASE WHEN purchase_transactions.status = "received" THEN 1 END) as on_time_deliveries'),
                    DB::raw('COUNT(CASE WHEN purchase_transactions.status = "cancelled" THEN 1 END) as cancelled_orders'),
                    DB::raw('AVG(CASE WHEN procurements.status = "received" THEN 1 WHEN procurements.status = "cancelled" THEN 0 END) * 100 as quality_score'),
                    DB::raw('SUM(purchase_transactions.total_amount) as total_purchase_value')
                )
                ->groupBy('suppliers.id', 'suppliers.supplier_name', 'suppliers.supplier_code')
                ->get();

            $this->reportData = [
                'suppliers' => $suppliers,
                'summary' => [
                    'total_suppliers' => $suppliers->count(),
                    'average_quality_score' => $suppliers->avg('quality_score'),
                    'total_transactions' => $suppliers->sum('total_transactions'),
                    'total_purchase_value' => $suppliers->sum('total_purchase_value')
                ]
            ];
        }

        // supplier transaction
        if ($this->report_type === 'supplier_transaction') {
            $transactions = DB::table('purchase_transactions')
                ->join('suppliers', 'purchase_transactions.supplier_id', '=', 'suppliers.id')
                ->leftJoin('purchase_items', 'purchase_transactions.id', '=', 'purchase_items.purchase_transaction_id')
                ->whereBetween('purchase_transactions.transaction_date', [$this->start_date, $this->end_date])
                ->select(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'purchase_transactions.transaction_date',
                    'purchase_transactions.status',
                    'purchase_transactions.total_amount',
                    DB::raw('COUNT(purchase_items.id) as total_items'),
                    DB::raw('SUM(purchase_items.quantity) as total_quantity')
                )
                ->groupBy(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'purchase_transactions.transaction_date',
                    'purchase_transactions.status',
                    'purchase_transactions.total_amount'
                )
                ->orderBy('purchase_transactions.transaction_date', 'desc')
                ->get();

            $procurements = DB::table('procurements')
                ->join('suppliers', 'procurements.supplier_id', '=', 'suppliers.id')
                ->leftJoin('procurement_items', 'procurements.id', '=', 'procurement_items.procurement_id')
                ->whereBetween('procurements.procurement_date', [$this->start_date, $this->end_date])
                ->select(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'procurements.procurement_date as transaction_date',
                    'procurements.status',
                    'procurements.total_cost as total_amount',
                    DB::raw('COUNT(procurement_items.id) as total_items'),
                    DB::raw('SUM(procurement_items.quantity) as total_quantity')
                )
                ->groupBy(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'procurements.procurement_date',
                    'procurements.status',
                    'procurements.total_cost'
                )
                ->orderBy('procurements.procurement_date', 'desc')
                ->get();

            $this->reportData = [
                'transactions' => $transactions->concat($procurements)->sortByDesc('transaction_date'),
                'summary' => [
                    'total_transactions' => $transactions->count() + $procurements->count(),
                    'total_amount' => $transactions->sum('total_amount') + $procurements->sum('total_amount'),
                    'total_items' => $transactions->sum('total_items') + $procurements->sum('total_items'),
                    'total_quantity' => $transactions->sum('total_quantity') + $procurements->sum('total_quantity')
                ]
            ];
        }

        // supplier payment
        if ($this->report_type === 'outstanding_payments') {
            // Get outstanding payments from purchase transactions
            $purchasePayments = DB::table('purchase_transactions')
                ->join('suppliers', 'purchase_transactions.supplier_id', '=', 'suppliers.id')
                ->whereBetween('purchase_transactions.transaction_date', [$this->start_date, $this->end_date])
                ->where('purchase_transactions.status', 'pending')
                ->select(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'purchase_transactions.transaction_date',
                    'purchase_transactions.total_amount',
                    DB::raw('"purchase" as transaction_type')
                );

            $procurementPayments = DB::table('procurements')
                ->join('suppliers', 'procurements.supplier_id', '=', 'suppliers.id')
                ->whereBetween('procurements.procurement_date', [$this->start_date, $this->end_date])
                ->where('procurements.status', 'ordered')
                ->select(
                    'suppliers.supplier_name',
                    'suppliers.supplier_code',
                    'procurements.procurement_date as transaction_date',
                    'procurements.total_cost as total_amount',
                    DB::raw('"procurement" as transaction_type')
                );

            $outstandingPayments = $purchasePayments->union($procurementPayments)
                ->orderBy('transaction_date')
                ->get();

            $this->reportData = [
                'payments' => $outstandingPayments,
                'summary' => [
                    'total_outstanding' => $outstandingPayments->sum('total_amount'),
                    'total_transactions' => $outstandingPayments->count(),
                    'suppliers_count' => $outstandingPayments->unique('supplier_code')->count(),
                    'average_amount' => $outstandingPayments->avg('total_amount')
                ]
            ];
        }
    }

    public function printReport()
    {

        if ($this->report_type === 'outstanding_payments') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.outstanding-payments', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'outstanding-payments-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'supplier_transaction') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.supplier-transaction', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'supplier-transaction-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'supplier_performance') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.supplier-performance', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'supplier-performance-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'budget_utilization') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.budget-utilization', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'budget-utilization-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'resource_allocation') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.resource-allocation', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'resource-allocation-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'milestone_progress') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.milestone-progress', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'milestone-progress-report-' . now()->format('Y-m-d') . '.pdf');
        }


        if ($this->report_type === 'project_status') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.project-status', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'project-status-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'fulfillment_time') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.fulfillment-time', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'fulfillment-time-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'stock_movement') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.stock-movement', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'stock-movement-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'inventory_valuation') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.inventory-valuation', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'inventory-valuation-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'profit_loss') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.profit-loss', $data);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'profit-loss-statement-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'balance_sheet') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.balance-sheet', $data);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'balance-sheet-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'cash_flow') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.cash-flow', $data);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'cash-flow-statement-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'expense_analysis') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.expense-analysis', $data);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'expense-analysis-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'sales_summary') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.sales-summary', $data);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'sales-summary-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'product_performance') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.product-performance', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'product-performance-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'customer_sales') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.customer-sales', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'customer-sales-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'sales_forecast') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.sales-forecast', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'sales-forecast-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'customer_retention') {
            $data = [
                'reportData' => $this->reportData,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.customer-retention', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'customer-retention-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'customer_feedback') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.customer-feedback', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'customer-feedback-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'lead_conversion') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.lead-conversion', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'lead-conversion-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'employee_attendance') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name
            ];

            $pdf = app(PDF::class)->loadView('reports.employee-attendance', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'employee-attendance-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'payroll') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.payroll', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'payroll-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'employee_turnover') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.employee-turnover', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'employee-turnover-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'employee_performance') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.employee-performance', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'employee-performance-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($this->report_type === 'stock_level') {
            $data = [
                'reportData' => $this->reportData,
                'company' => Filament::getTenant()->name,
                'generated_at' => now()->format('d M Y H:i:s'),
                'generated_by' => Auth::user()->name,
                'period' => [
                    'start' => $this->start_date,
                    'end' => $this->end_date
                ]
            ];

            $pdf = app(PDF::class)->loadView('reports.stock-level', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'stock-level-report-' . now()->format('Y-m-d') . '.pdf');
        }
    }

    public function render()
    {
        return view('livewire.report');
    }
}
