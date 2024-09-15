@extends('layout')

@section('content')
    < class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-4 text-center mb-4">Income Statement Report</h1>
                <h2 class="h3 text-center mb-3">{{ $incomeStatement->financialReport->report_name }}</h2>
                <p class="text-center mb-5"><strong>Period:</strong>
                    {{ $incomeStatement->financialReport->report_period_start }} to
                    {{ $incomeStatement->financialReport->report_period_end }}</p>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0">Revenue</h3>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <span>Total Revenue</span>
                                <span class="h4 mb-0">{{ number_format($incomeStatement->total_revenue, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-danger text-white">
                                <h3 class="h5 mb-0">Expenses</h3>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <span>Total Expenses</span>
                                <span class="h4 mb-0">{{ number_format($incomeStatement->total_expenses, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h3 class="h5 mb-0">Net Income</h3>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <span>Net Income</span>
                                <span class="h4 mb-0">{{ number_format($incomeStatement->net_income, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <tr>
                                        <th class="font-weight-normal">Total Revenue</th>
                                        <td class="text-right">{{ number_format($incomeStatement->total_revenue, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="font-weight-normal">Total Expenses</th>
                                        <td class="text-right">{{ number_format($incomeStatement->total_expenses, 2) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <th class="font-weight-bold">Net Income</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($incomeStatement->net_income, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($incomeStatement->financialReport->notes)
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="h5 mb-0">Notes</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $incomeStatement->financialReport->notes }}</p>
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
