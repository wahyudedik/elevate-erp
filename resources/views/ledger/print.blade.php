@extends('layout')

@section('content')
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Ledger</h1>
            <div class="invoice-details">
                <p><strong>Date:</strong> {{ $ledger->transaction_date }}</p>
                <p><strong>Invoice #:</strong> LED-{{ $ledger->id }}</p>
            </div>
        </div>

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
            </table>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>

    <style>
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .invoice-header h1 {
            color: #333;
            margin: 0;
        }

        .invoice-details {
            text-align: right;
        }

        .account-info,
        .transactions {
            margin-bottom: 30px;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .transaction-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        .transaction-table .amount {
            text-align: right;
        }

        .invoice-footer {
            margin-top: 30px;
            text-align: center;
            color: #7f8c8d;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
@endsection
