@extends('layout')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-4 text-center mb-4">Financial Report</h1>
                <h2 class="h3 text-center mb-3">{{ $financialReport->report_name }}</h2>
                <p class="text-center mb-5">
                    <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $financialReport->report_type)) }}<br>
                    <strong>Period:</strong> {{ $financialReport->report_period_start }} to
                    {{ $financialReport->report_period_end }}
                </p>

                @if ($financialReport->report_type == 'income_statement')
                    <div class="card shadow-sm mb-5">
                        <div class="card-header bg-info text-white">
                            <h3 class="h5 mb-0">Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless">
                                    <thead>
                                        <tr class="bg-light">
                                            <th scope="col" class="text-muted text-uppercase">Description</th>
                                            <th scope="col" class="text-right text-muted text-uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($financialReport->incomeStatement as $incomeStatement)
                                            <tr>
                                                <th class="font-weight-normal">Total Revenue</th>
                                                <td class="text-right">
                                                    {{ number_format($incomeStatement->total_revenue, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="font-weight-normal">Total Expenses</th>
                                                <td class="text-right">
                                                    {{ number_format($incomeStatement->total_expenses, 2) }}
                                                </td>
                                            </tr>
                                            <tr class="border-top">
                                                <th class="font-weight-bold">Net Income</th>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($incomeStatement->net_income, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif($financialReport->report_type == 'balance_sheet')
                    <div class="card shadow-sm mb-5">
                        <div class="card-header bg-info text-white">
                            <h3 class="h5 mb-0">Balance Sheet Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless">
                                    <thead>
                                        <tr class="bg-light">
                                            <th scope="col" class="text-muted text-uppercase">Description</th>
                                            <th scope="col" class="text-right text-muted text-uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($financialReport->balanceSheet as $balanceSheet)
                                            <tr>
                                                <th class="font-weight-normal">Total Assets</th>
                                                <td class="text-right">
                                                    {{ number_format($balanceSheet->total_assets, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="font-weight-normal">Total Liabilities</th>
                                                <td class="text-right">
                                                    {{ number_format($balanceSheet->total_liabilities, 2) }}
                                                </td>
                                            </tr>
                                            <tr class="border-top">
                                                <th class="font-weight-bold">Total Equity</th>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($balanceSheet->total_equity, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif($financialReport->report_type == 'cash_flow')
                    <div class="card shadow-sm mb-5">
                        <div class="card-header bg-info text-white">
                            <h3 class="h5 mb-0">Cash Flow Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless">
                                    <thead>
                                        <tr class="bg-light">
                                            <th scope="col" class="text-muted text-uppercase">Description</th>
                                            <th scope="col" class="text-right text-muted text-uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($financialReport->cashFlow as $cashFlow)
                                            <tr>
                                                <th class="font-weight-normal">Operating Cash Flow</th>
                                                <td class="text-right">
                                                    {{ number_format($cashFlow->operating_cash_flow, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="font-weight-normal">Investing Cash Flow</th>
                                                <td class="text-right">
                                                    {{ number_format($cashFlow->investing_cash_flow, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="font-weight-normal">Financing Cash Flow</th>
                                                <td class="text-right">
                                                    {{ number_format($cashFlow->financing_cash_flow, 2) }}
                                                </td>
                                            </tr>
                                            <tr class="border-top">
                                                <th class="font-weight-bold">Net Cash Flow</th>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($cashFlow->net_cash_flow, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($financialReport->notes)
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="h5 mb-0">Notes</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $financialReport->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.print();
    </script>
@endsection
