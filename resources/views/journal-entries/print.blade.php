@extends('layout')

@section('content')
    <div class="container my-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-3">
                <h1 class="card-title mb-0">Journal Entry #{{ $journalEntry->id }}</h1>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-box bg-light p-3 rounded">
                            <h6 class="text-muted mb-2">Date</h6>
                            <p class="h4">{{ $journalEntry->entry_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light p-3 rounded">
                            <h6 class="text-muted mb-2">Type</h6>
                            <p class="h4">{{ ucfirst($journalEntry->entry_type) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light p-3 rounded">
                            <h6 class="text-muted mb-2">Amount</h6>
                            <p class="h4">IDR {{ number_format($journalEntry->amount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <h2 class="h3 mb-4 text-primary">Account Details</h2>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="info-box border p-3 rounded">
                            <h6 class="text-muted mb-2">Account Name</h6>
                            <p class="h5">{{ $account->account_name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-box border p-3 rounded">
                            <h6 class="text-muted mb-2">Account Type</h6>
                            <p class="h5">{{ ucfirst($account->account_type) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-box border p-3 rounded">
                            <h6 class="text-muted mb-2">Initial Balance</h6>
                            <p class="h5">IDR {{ number_format($account->initial_balance, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-box border p-3 rounded">
                            <h6 class="text-muted mb-2">Current Balance</h6>
                            <p class="h5">IDR {{ number_format($account->current_balance, 2) }}</p>
                        </div>
                    </div>
                </div>

                <h2 class="h3 mb-4 text-primary">Journal Entry Description</h2>
                <div class="bg-light p-4 rounded">
                    <p class="mb-0 lead">{{ $journalEntry->description ?? 'No description provided.' }}</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.print()
    </script>
@endsection
