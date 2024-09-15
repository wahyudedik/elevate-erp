@extends('layout')

@section('content')
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-4 mb-4 text-center">Cash Flow Report</h1>
                <h2 class="h3 mb-3 text-center">{{ $cashFlow->financialReport->report_name }}</h2>
                <p class="mb-5 text-center"><strong>Period:</strong>
                    {{ $cashFlow->financialReport->report_period_start }} to
                    {{ $cashFlow->financialReport->report_period_end }}</p>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0">Operating Cash Flow</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Amount</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($cashFlow->operating_cash_flow, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h3 class="h5 mb-0">Investing Cash Flow</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Amount</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($cashFlow->investing_cash_flow, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h3 class="h5 mb-0">Financing Cash Flow</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Amount</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($cashFlow->financing_cash_flow, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-warning text-white">
                                <h3 class="h5 mb-0">Net Cash Flow</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Amount</th>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($cashFlow->net_cash_flow, 2) }}</td>
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
                                    <th>Operating Cash Flow</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($cashFlow->operating_cash_flow, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Investing Cash Flow</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($cashFlow->investing_cash_flow, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Financing Cash Flow</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($cashFlow->financing_cash_flow, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Net Cash Flow</th>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($cashFlow->net_cash_flow, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($cashFlow->financialReport->notes)
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="h5 mb-0">Notes</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $cashFlow->financialReport->notes }}</p>
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
