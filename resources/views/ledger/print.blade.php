@extends('layout')

@section('content')
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-logo">
                <img src="{{ asset('home/assets/img/3-removebg-preview.png') }}" alt="Company Logo">
            </div>
            <div class="invoice-title">
                <h1>Ledger</h1>
                <div class="invoice-details">
                    <p><strong>Date:</strong> {{ $ledger->transaction_date }}</p>
                    <p><strong>Invoice #:</strong> LED-{{ $ledger->id }}</p>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="account-info">
                <h2>Account Details</h2>
                <p><strong>Account Name:</strong> {{ $account->account_name }}</p>
                <p><strong>Account Type:</strong> {{ $account->account_type }}</p>
            </div>

            <div class="ledger-info">
                <h2>Ledger Details</h2>
                <p><strong>Transaction Type:</strong> {{ $ledger->transaction_type }}</p>
                <p><strong>Amount:</strong> {{ number_format($ledger->amount, 2) }}</p>
                <p><strong>Description:</strong> {{ $ledger->transaction_description }}</p>
            </div>
        </div>

        <div class="transactions">
            <h2>Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_number }}</td>
                            <td>{{ $transaction->notes }}</td>
                            <td class="amount">{{ number_format($transaction->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="total-label">Total</td>
                        <td class="total-amount">{{ number_format($transactions->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- <div class="invoice-footer">
            <p>Thank you for your business!</p>
            <div class="barcode">
                {!! DNS1D::getBarcodeHTML('LED-'.$ledger->id, 'C39+') !!}
            </div>
        </div> --}}
    </div>

    <style>
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #ffffff;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }

        .company-logo img {
            max-width: 150px;
            height: auto;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 36px;
            font-weight: 700;
        }

        .invoice-details {
            margin-top: 10px;
            font-size: 14px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .account-info,
        .ledger-info {
            flex-basis: 48%;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            font-size: 24px;
            margin-top: 0;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .transaction-table th,
        .transaction-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .transaction-table th {
            background-color: #3498db;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
        }

        .transaction-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .transaction-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .transaction-table tfoot {
            font-weight: bold;
            background-color: #ecf0f1;
        }

        .transaction-table .total-label {
            text-align: right;
        }

        .transaction-table .total-amount {
            text-align: right;
            color: #3498db;
        }

        .invoice-footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            border-top: 2px solid #3498db;
            padding-top: 20px;
        }

        .barcode {
            margin-top: 20px;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
@endsection