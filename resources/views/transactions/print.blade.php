@extends('layout')

@section('content')
    <div class="container-fluid p-5"
        style="max-width: 210mm; min-height: 297mm; background-color: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.15); border-radius: 10px;">
        <div class="text-center mb-5">
            <h1 class="display-4 font-weight-bold text-primary">Transaction Details</h1>
            <hr class="my-4" style="width: 50%; margin: auto; border-top: 2px solid #007bff;">
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width: 40%;">Ledger ID</th>
                            <td class="font-weight-bold">{{ $transaction->ledger_id }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Transaction Date</th>
                            <td class="font-weight-bold">{{ $ledger->transaction_date }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Transaction Type</th>
                            <td class="font-weight-bold">{{ $ledger->transaction_type }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Amount</th>
                            <td class="font-weight-bold text-success">{{ number_format($transaction->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Transaction Description</th>
                            <td class="font-weight-bold">{{ $ledger->transaction_description }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Transaction Number</th>
                            <td class="font-weight-bold">{{ $transaction->transaction_number }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td class="font-weight-bold">{{ $transaction->status }}
                                {{-- <span class="badge badge-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : ($transaction->status == 'failed' ? 'danger' : 'secondary')) }}">
                                    {{ $transaction->status }}
                                </span> --}}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Notes</th>
                            <td class="font-weight-bold">{{ $transaction->notes }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-center mt-5">
            <small class="text-muted">Printed on {{ date('Y-m-d H:i:s') }}</small>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
@endsection
