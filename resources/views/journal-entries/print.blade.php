@extends('layout')

@section('content')
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title mb-4 text-primary">Journal Entry #{{ $journalEntry->id }}</h1>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="mb-2"><strong class="text-muted">Date:</strong></p>
                        <p class="lead">{{ $journalEntry->entry_date->format('Y-m-d') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-2"><strong class="text-muted">Type:</strong></p>
                        <p class="lead">{{ ucfirst($journalEntry->entry_type) }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-2"><strong class="text-muted">Amount:</strong></p>
                        <p class="lead">IDR {{ number_format($journalEntry->amount, 2) }}</p>
                    </div>
                </div>

                <h2 class="h4 mb-3 text-secondary">Account Details</h2>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2"><strong class="text-muted">Account Name:</strong></p>
                        <p>{{ $account->account_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong class="text-muted">Account Type:</strong></p>
                        <p>{{ ucfirst($account->account_type) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong class="text-muted">Initial Balance:</strong></p>
                        <p>IDR {{ number_format($account->initial_balance, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong class="text-muted">Current Balance:</strong></p>
                        <p>IDR {{ number_format($account->current_balance, 2) }}</p>
                    </div>
                </div>

                <h2 class="h4 mb-3 text-secondary">Journal Entry Description</h2>
                <p class="mb-0">{{ $journalEntry->description ?? 'No description provided.' }}</p>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
@endsection
