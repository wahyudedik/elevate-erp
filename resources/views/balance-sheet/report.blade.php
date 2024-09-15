@extends('layout')

@section('content')
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-4 mb-4 text-center">Balance Sheet Report</h1>
                <h2 class="h3 mb-3 text-center">{{ $balanceSheet->financialReport->report_name }}</h2>
                <p class="mb-5 text-center"><strong>Periode:</strong>
                    {{ $balanceSheet->financialReport->report_period_start }} sampai
                    {{ $balanceSheet->financialReport->report_period_end }}</p>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-primary text-white">

                                <h3 class="h5 mb-0">Assets</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Total Assets</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($balanceSheet->total_assets, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h3 class="h5 mb-0">Liabilities</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Total Liabilities</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($balanceSheet->total_liabilities, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h3 class="h5 mb-0">Equity</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Total Equity</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($balanceSheet->total_equity, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-5">
                    <div class="card-header bg-dark text-white">
                        <h3 class="h5 mb-0">Summary</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Description</th>
                                    <th scope="col" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Total Assets</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($balanceSheet->total_assets, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Liabilities</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($balanceSheet->total_liabilities, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Equity</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($balanceSheet->total_equity, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($balanceSheet->financialReport->notes)
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="h5 mb-0">Notes</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $balanceSheet->financialReport->notes }}</p>
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
