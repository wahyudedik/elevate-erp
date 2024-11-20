<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">List Report for ERP Application</h1>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" wire:model="start_date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" wire:model="end_date"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select wire:model="report_type"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Report Type</option>
                    <optgroup label="Financial Reports">
                        <option value="profit_loss">Profit and Loss Statement</option>
                        <option value="balance_sheet">Balance Sheet</option>
                        <option value="cash_flow">Cash Flow Report</option>
                        <option value="expense_analysis">Expense Analysis Report</option>
                    </optgroup>
                    <optgroup label="Sales Reports">
                        <option value="sales_summary">Sales Summary</option>
                        <option value="product_performance">Product Performance Report</option>
                        <option value="customer_sales">Customer Sales Report</option>
                        <option value="sales_forecast">Sales Forecast</option>
                    </optgroup>
                    <optgroup label="Customer Reports">
                        <option value="customer_retention">Customer Retention Report</option>
                        <option value="customer_feedback">Customer Feedback Summary</option>
                        <option value="lead_conversion">Lead Conversion Report</option>
                    </optgroup>
                    <optgroup label="HR Reports">
                        <option value="employee_attendance">Employee Attendance Report</option>
                        <option value="payroll">Payroll Report</option>
                        <option value="employee_turnover">New Hires vs Exits Report</option>
                        <option value="employee_performance">Employee Performance Report</option>
                    </optgroup>
                    <optgroup label="Inventory Reports">
                        <option value="stock_level">Stock Level Report</option>
                        <option value="inventory_valuation">Inventory Valuation Report</option>
                        <option value="stock_movement">Stock Movement Report</option>
                        <option value="fulfillment_time">Fulfillment Time Report</option>
                    </optgroup>
                    <optgroup label="Project Reports">
                        <option value="project_status">Project Status Report</option>
                        <option value="milestone_progress">Milestone Progress Report</option>
                        <option value="resource_allocation">Resource Allocation Report</option>
                        <option value="budget_utilization">Budget Utilization Report</option>
                    </optgroup>
                    <optgroup label="Supplier Reports">
                        <option value="supplier_performance">Supplier Performance Report</option>
                        <option value="supplier_transaction">Supplier Transaction Report</option>
                        <option value="outstanding_payments">Outstanding Payments Report</option>
                    </optgroup>
                </select>
            </div>
        </div>
    </div>
    <div class="flex justify-end space-x-4 mt-4">
        <button type="button" wire:click="generateReport"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-base rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Generate Report
        </button>
        <button type="button" wire:click="printReport"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-base rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    @if ($report_type === 'profit_loss' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Profit and Loss Statement</h2>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-green-600">Total Revenue</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['revenue'], 2) }}</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-red-600">Total Expenses</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['expenses'], 2) }}</p>
                    </div>
                    <div class="bg-{{ $reportData['net_income'] >= 0 ? 'blue' : 'red' }}-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-{{ $reportData['net_income'] >= 0 ? 'blue' : 'red' }}-600">
                            Net Income/Loss</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['net_income'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'balance_sheet' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Balance Sheet</h2>

            <!-- Assets Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Assets</h3>
                <div class="space-y-2">
                    @foreach ($reportData['assets'] as $asset)
                        <div class="flex justify-between px-4 py-2 bg-gray-50 rounded">
                            <span>{{ $asset['name'] }}</span>
                            <span class="font-medium">Rp {{ number_format($asset['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-4 py-2 bg-blue-50 rounded font-bold">
                        <span>Total Assets</span>
                        <span>Rp {{ number_format($reportData['total_assets'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Liabilities Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Liabilities</h3>
                <div class="space-y-2">
                    @foreach ($reportData['liabilities'] as $liability)
                        <div class="flex justify-between px-4 py-2 bg-gray-50 rounded">
                            <span>{{ $liability['name'] }}</span>
                            <span class="font-medium">Rp {{ number_format($liability['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-4 py-2 bg-red-50 rounded font-bold">
                        <span>Total Liabilities</span>
                        <span>Rp {{ number_format($reportData['total_liabilities'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Equity Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Equity</h3>
                <div class="space-y-2">
                    @foreach ($reportData['equity'] as $equity)
                        <div class="flex justify-between px-4 py-2 bg-gray-50 rounded">
                            <span>{{ $equity['name'] }}</span>
                            <span class="font-medium">Rp {{ number_format($equity['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-4 py-2 bg-green-50 rounded font-bold">
                        <span>Total Equity</span>
                        <span>Rp {{ number_format($reportData['total_equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'cash_flow' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Cash Flow Statement</h2>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-600">Operating Cash Flow</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['operating_cash_flow'], 2) }}
                        </p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-green-600">Investing Cash Flow</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['investing_cash_flow'], 2) }}
                        </p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-purple-600">Financing Cash Flow</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['financing_cash_flow'], 2) }}
                        </p>
                    </div>
                    <div class="bg-{{ $reportData['net_cash_flow'] >= 0 ? 'green' : 'red' }}-50 p-4 rounded-lg">
                        <h3
                            class="text-sm font-medium text-{{ $reportData['net_cash_flow'] >= 0 ? 'green' : 'red' }}-600">
                            Net Cash Flow</h3>
                        <p class="text-2xl font-bold">Rp {{ number_format($reportData['net_cash_flow'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'expense_analysis' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Expense Analysis Report</h2>

            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900">Total Expenses: Rp
                    {{ number_format($reportData['total_expenses'], 2) }}</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Percentage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transactions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['expenses'] as $category => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($data['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($data['percentage'], 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $data['transactions'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'sales_summary' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Sales Summary Report</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_transactions']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Sales</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['total_sales'], 2) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Average Sale</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['average_sale'], 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Unique Customers</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['unique_customers']) }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'product_performance' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Product Performance Report</h2>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Revenue</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['total_revenue'], 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_transactions']) }}</p>
                </div>
            </div>

            <!-- Top Products -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Top 5 Products</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Transactions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['top_products'] as $product)
                                <tr>
                                    <td class="px-6 py-4">{{ $product->product_name }}</td>
                                    <td class="px-6 py-4">{{ number_format($product->total_quantity) }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($product->total_revenue, 2) }}</td>
                                    <td class="px-6 py-4">{{ number_format($product->transaction_count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Products -->
            <div>
                <h3 class="text-lg font-semibold mb-3">Bottom 5 Products</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Transactions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['bottom_products'] as $product)
                                <tr>
                                    <td class="px-6 py-4">{{ $product->product_name }}</td>
                                    <td class="px-6 py-4">{{ number_format($product->total_quantity) }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($product->total_revenue, 2) }}</td>
                                    <td class="px-6 py-4">{{ number_format($product->transaction_count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'customer_sales' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Sales Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Revenue</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['total_revenue'], 2) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_transactions']) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Average Customer Value</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['average_customer_value'], 2) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase
                                Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average
                                Transaction</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['customers'] as $customer)
                            <tr>
                                <td class="px-6 py-4">{{ $customer->customer_name }}</td>
                                <td class="px-6 py-4">{{ number_format($customer->purchase_frequency) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($customer->total_spent, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($customer->average_transaction, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'sales_forecast' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Sales Forecast Report</h2>

            <div class="mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Average Monthly Growth Rate</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['average_growth_rate'], 2) }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Historical Data -->
                <div>
                    <h3 class="text-lg font-medium mb-3">Historical Sales</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($reportData['historical_data'] as $data)
                                    <tr>
                                        <td class="px-6 py-4">{{ $data['month'] }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($data['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Forecast Data -->
                <div>
                    <h3 class="text-lg font-medium mb-3">Sales Forecast (Next 6 Months)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Forecast Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Growth
                                        Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($reportData['forecast_data'] as $forecast)
                                    <tr>
                                        <td class="px-6 py-4">{{ $forecast['month'] }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($forecast['amount'], 2) }}</td>
                                        <td class="px-6 py-4">{{ number_format($forecast['growth_rate'], 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'customer_retention' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Retention Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Previous Period Customers</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_previous_customers']) }}</p>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Returning Customers</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['returning_customers']) }}</p>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Retention Rate</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['retention_rate'], 1) }}%</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['customer_details'] as $customer)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $customer->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Returning Customer
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'customer_feedback' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Feedback Summary</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Feedback</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_feedback']) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">High Priority</h3>
                    <p class="text-2xl font-bold">{{ $reportData['priority_summary']['high_priority'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Medium Priority</h3>
                    <p class="text-2xl font-bold">{{ $reportData['priority_summary']['medium_priority'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Low Priority</h3>
                    <p class="text-2xl font-bold">{{ $reportData['priority_summary']['low_priority'] }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['feedback_list'] as $feedback)
                            <tr>
                                <td class="px-6 py-4">{{ $feedback->subject }}</td>
                                <td class="px-6 py-4">{{ $feedback->description }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $feedback->priority === 'high'
                                        ? 'bg-red-100 text-red-800'
                                        : ($feedback->priority === 'medium'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($feedback->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ ucfirst($feedback->status) }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($feedback->created_at)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'lead_conversion' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Lead Conversion Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Leads</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['total_leads']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Converted Leads</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['converted_leads']) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Conversion Rate</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['conversion_rate'], 1) }}%</p>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium mb-3">Conversion by Stage</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['stage_conversion'] as $stage => $count)
                                <tr>
                                    <td class="px-6 py-4">{{ $stage }}</td>
                                    <td class="px-6 py-4">{{ number_format($count) }}</td>
                                    <td class="px-6 py-4">
                                        {{ number_format(($count / $reportData['total_leads']) * 100, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium mb-3">Recent Conversions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['recent_conversions'] as $conversion)
                                <tr>
                                    <td class="px-6 py-4">{{ $conversion->name }}</td>
                                    <td class="px-6 py-4">{{ $conversion->email }}</td>
                                    <td class="px-6 py-4">
                                        {{ json_decode($conversion->interaction_type)->stage ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ Carbon\Carbon::parse($conversion->created_at)->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'employee_attendance' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Employee Attendance Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                @foreach ($reportData['summary'] as $employee)
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="font-medium text-gray-900">{{ $employee->first_name }} {{ $employee->last_name }}
                        </h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600">Present: {{ $employee->present_days }}</p>
                            <p class="text-sm text-gray-600">Late: {{ $employee->late_days }}</p>
                            <p class="text-sm text-gray-600">Absent: {{ $employee->absent_days }}</p>
                            <p class="text-sm text-gray-600">Leave: {{ $employee->leave_days }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Detailed Attendance Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['attendances'] as $attendance)
                            <tr>
                                <td class="px-6 py-4">{{ $attendance->first_name }} {{ $attendance->last_name }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4">{{ $attendance->check_in }}</td>
                                <td class="px-6 py-4">{{ $attendance->check_out }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $attendance->status === 'present'
                                        ? 'bg-green-100 text-green-800'
                                        : ($attendance->status === 'late'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : ($attendance->status === 'absent'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $attendance->note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'payroll' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Payroll Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Payroll</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_payroll'], 2) }}
                    </p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Employees</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_employees']) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Average Salary</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['average_salary'], 2) }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Basic Salary
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allowances</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deductions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Salary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['payroll_details'] as $payroll)
                            <tr>
                                <td class="px-6 py-4">{{ $payroll->first_name }} {{ $payroll->last_name }}</td>
                                <td class="px-6 py-4">{{ $payroll->employee_code }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($payroll->basic_salary, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($payroll->allowances, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($payroll->deductions, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($payroll->net_salary, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $payroll->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($payroll->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'employee_turnover' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Employee Turnover Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">New Hires</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['total_new_hires'] }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Exits</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['total_exits'] }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Net Change</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['net_change'] }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Turnover Rate</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['turnover_rate'], 1) }}%
                    </p>
                </div>
            </div>

            <!-- New Hires Table -->
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-3">New Hires</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Join Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['new_hires'] as $employee)
                                <tr>
                                    <td class="px-6 py-4">{{ $employee->first_name }} {{ $employee->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $employee->employee_code }}</td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::parse($employee->date_of_joining)->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Exits Table -->
            <div>
                <h3 class="text-lg font-medium mb-3">Exits</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exit Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reportData['exits'] as $employee)
                                <tr>
                                    <td class="px-6 py-4">{{ $employee->first_name }} {{ $employee->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $employee->employee_code }}</td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::parse($employee->exit_date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4">{{ ucfirst($employee->exit_type) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($report_type === 'employee_performance' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Employee Performance Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Employees</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_employees']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Average Attendance Rate</h3>
                    <p class="text-2xl font-bold">
                        {{ number_format($reportData['summary']['average_attendance'], 1) }}%</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Overall Performance</h3>
                    <p class="text-2xl font-bold">
                        {{ number_format($reportData['summary']['average_performance'], 1) }}%</p>
                </div>
            </div>

            <!-- Performance Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attendance Rate
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Late Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performance
                                Score</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['employees'] as $employee)
                            <tr>
                                <td class="px-6 py-4">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                <td class="px-6 py-4">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4">{{ number_format($employee->attendance_rate, 1) }}%</td>
                                <td class="px-6 py-4">{{ $employee->late_count }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ ($employee->attendance_rate - $employee->late_count >= 90
                                            ? 'bg-green-100 text-green-800'
                                            : $employee->attendance_rate - $employee->late_count >= 75)
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-red-100 text-red-800' }}">
                                        {{ number_format($employee->attendance_rate - $employee->late_count, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'stock_level' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Stock Level Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Items</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_items']) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Out of Stock</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['out_of_stock']) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Low Stock</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['low_stock']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Inventory Value</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_value'], 2) }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['inventories'] as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->item_name }}</td>
                                <td class="px-6 py-4">{{ $item->sku }}</td>
                                <td class="px-6 py-4">{{ number_format($item->quantity) }}</td>
                                <td class="px-6 py-4">{{ $item->location }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $item->status === 'in_stock'
                                        ? 'bg-green-100 text-green-800'
                                        : ($item->status === 'out_of_stock'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">Rp
                                    {{ number_format($item->quantity * $item->purchase_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'inventory_valuation' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Inventory Valuation Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Items</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_items']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Purchase Value</h3>
                    <p class="text-2xl font-bold">Rp
                        {{ number_format($reportData['summary']['total_purchase_value'], 2) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Total Potential Value</h3>
                    <p class="text-2xl font-bold">Rp
                        {{ number_format($reportData['summary']['total_potential_value'], 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Total Quantity</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_quantity']) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selling Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Value
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['inventories'] as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->item_name }}</td>
                                <td class="px-6 py-4">{{ $item->sku }}</td>
                                <td class="px-6 py-4">{{ number_format($item->quantity) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($item->purchase_price, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($item->selling_price, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($item->total_value, 2) }}</td>
                                <td class="px-6 py-4">{{ $item->location }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'stock_movement' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Stock Movement Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Movements</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_movements']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Stock Additions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_additions']) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Stock Deductions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_deductions']) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Net Change</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['net_change']) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['movements'] as $movement)
                            <tr>
                                <td class="px-6 py-4">{{ $movement->item_name }}</td>
                                <td class="px-6 py-4">{{ $movement->sku }}</td>
                                <td class="px-6 py-4">{{ number_format($movement->quantity_before) }}</td>
                                <td class="px-6 py-4">{{ number_format($movement->quantity_after) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $movement->transaction_type === 'addition' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($movement->transaction_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $movement->remarks }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($movement->transaction_date)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'fulfillment_time' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Fulfillment Time Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Average Fulfillment Time</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['average_time'], 1) }}
                        hours</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Orders Under 24h</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['orders_under_24h']) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Orders Over 48h</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['orders_over_48h']) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fulfillment
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Taken</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['orders'] as $order)
                            <tr>
                                <td class="px-6 py-4">{{ $order->order_number }}</td>
                                <td class="px-6 py-4">{{ $order->customer_name }}</td>
                                <td class="px-6 py-4">
                                    {{ Carbon\Carbon::parse($order->order_date)->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    {{ Carbon\Carbon::parse($order->fulfillment_date)->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm 
                                    {{ $order->fulfillment_hours <= 24
                                        ? 'bg-green-100 text-green-800'
                                        : ($order->fulfillment_hours > 48
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ number_format($order->fulfillment_hours, 1) }} hours
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'project_status' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Project Status Report</h2>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Projects</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['total_projects'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Active</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['active_projects'] }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Completed</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['completed_projects'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">On Hold</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['on_hold_projects'] }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Cancelled</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['cancelled_projects'] }}</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-indigo-600">Avg. Completion</h3>
                    <p class="text-2xl font-bold">
                        {{ number_format($reportData['summary']['average_completion'], 1) }}%</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['projects'] as $project)
                            <tr>
                                <td class="px-6 py-4">{{ $project->name }}</td>
                                <td class="px-6 py-4">{{ $project->client_name }}</td>
                                <td class="px-6 py-4">{{ $project->manager_first_name }}
                                    {{ $project->manager_last_name }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
                                <td class="px-6 py-4">
                                    {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $project->status === 'completed'
                                        ? 'bg-green-100 text-green-800'
                                        : ($project->status === 'in_progress'
                                            ? 'bg-blue-100 text-blue-800'
                                            : ($project->status === 'on_hold'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                            style="width: {{ $project->completion_percentage }}%"></div>
                                    </div>
                                    <span
                                        class="text-sm text-gray-600">{{ number_format($project->completion_percentage, 1) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'milestone_progress' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Milestone Progress Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Milestones</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['total_milestones'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Achieved</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['achieved_milestones'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Pending</h3>
                    <p class="text-2xl font-bold">{{ $reportData['summary']['pending_milestones'] }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Achievement Rate</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['achievement_rate'], 1) }}%
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Milestone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['milestones'] as $milestone)
                            <tr>
                                <td class="px-6 py-4">{{ $milestone->project_name }}</td>
                                <td class="px-6 py-4">{{ $milestone->client_name }}</td>
                                <td class="px-6 py-4">{{ $milestone->milestone_name }}</td>
                                <td class="px-6 py-4">{{ $milestone->milestone_description }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($milestone->milestone_date)->format('d M Y') }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $milestone->status === 'achieved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($milestone->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'resource_allocation' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Resource Allocation Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Resources</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_resources']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Cost</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_cost'], 2) }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Human Resources</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['human_resources']) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Material Resources</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['material_resources']) }}
                    </p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-indigo-600">Financial Resources</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['financial_resources']) }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resource Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allocation
                                Count</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['resources'] as $resource)
                            <tr>
                                <td class="px-6 py-4">{{ $resource->project_name }}</td>
                                <td class="px-6 py-4">
                                    @if ($resource->resource_type === 'human')
                                        {{ $resource->first_name }} {{ $resource->last_name }}
                                    @else
                                        {{ $resource->resource_name }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $resource->resource_type === 'human'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($resource->resource_type === 'material'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($resource->resource_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">Rp {{ number_format($resource->resource_cost, 2) }}</td>
                                <td class="px-6 py-4">{{ number_format($resource->allocation_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'budget_utilization' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Budget Utilization Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Projects</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_projects']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Budget</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_budget'], 2) }}
                    </p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Total Spent</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_spent'], 2) }}
                    </p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Average Utilization</h3>
                    <p class="text-2xl font-bold">
                        {{ number_format($reportData['summary']['average_utilization'], 1) }}%</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actual Cost
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilization
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['projects'] as $project)
                            <tr>
                                <td class="px-6 py-4">{{ $project->project_name }}</td>
                                <td class="px-6 py-4">{{ $project->client_name }}</td>
                                <td class="px-6 py-4">{{ $project->manager_first_name }}
                                    {{ $project->manager_last_name }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($project->planned_budget, 2) }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($project->actual_cost, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $project->budget_utilization_percentage <= 85
                                        ? 'bg-green-100 text-green-800'
                                        : ($project->budget_utilization_percentage <= 100
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                        {{ number_format($project->budget_utilization_percentage, 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                            style="width: {{ $project->completion_percentage }}%"></div>
                                    </div>
                                    <span
                                        class="text-sm text-gray-600">{{ number_format($project->completion_percentage, 1) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Add this after the buttons section -->
    @if ($report_type === 'supplier_performance' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Supplier Performance Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Suppliers</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_suppliers']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Average Quality Score</h3>
                    <p class="text-2xl font-bold">
                        {{ number_format($reportData['summary']['average_quality_score'], 2) }}%</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Total Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_transactions']) }}
                    </p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Total Purchase Value</h3>
                    <p class="text-2xl font-bold">Rp
                        {{ number_format($reportData['summary']['total_purchase_value'], 2) }}</p>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Transactions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                On-Time Deliveries</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quality Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Purchase Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['suppliers'] as $supplier)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $supplier->supplier_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $supplier->supplier_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($supplier->total_transactions) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($supplier->on_time_deliveries) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $supplier->quality_score }}%"></div>
                                        </div>
                                        <span class="ml-2">{{ number_format($supplier->quality_score, 2) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                    {{ number_format($supplier->total_purchase_value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'supplier_transaction' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Supplier Transaction Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Total Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_transactions']) }}
                    </p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Total Amount</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['total_amount'], 2) }}
                    </p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-600">Total Items</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_items']) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Total Quantity</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_quantity']) }}</p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['transactions'] as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->supplier_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->supplier_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction->status === 'received'
                                        ? 'bg-green-100 text-green-800'
                                        : ($transaction->status === 'pending'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ number_format($transaction->total_items) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ number_format($transaction->total_quantity) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp
                                    {{ number_format($transaction->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($report_type === 'outstanding_payments' && !empty($reportData))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Outstanding Payments Report</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-600">Total Outstanding</h3>
                    <p class="text-2xl font-bold">Rp
                        {{ number_format($reportData['summary']['total_outstanding'], 2) }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-600">Pending Transactions</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_transactions']) }}
                    </p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-600">Suppliers Count</h3>
                    <p class="text-2xl font-bold">{{ number_format($reportData['summary']['suppliers_count']) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-600">Average Amount</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($reportData['summary']['average_amount'], 2) }}
                    </p>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reportData['payments'] as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $payment->supplier_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $payment->supplier_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($payment->transaction_date)->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp
                                    {{ number_format($payment->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $payment->transaction_type === 'purchase' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($payment->transaction_type) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
