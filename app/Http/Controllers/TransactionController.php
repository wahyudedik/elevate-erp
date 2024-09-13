<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementFinancial\Transaction;

class TransactionController extends Controller
{
    public function print(Transaction $transaction)
    {
        $transaction->load('ledger.account');
        $ledger = $transaction->ledger;
        return view('transactions.print', compact('transaction', 'ledger'));
    }
}
