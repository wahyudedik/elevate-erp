<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Filament\Facades\Filament;
use App\Models\ManagementFinancial\Ledger;
use App\Models\ManagementFinancial\Transaction;
use Illuminate\Support\Facades\Auth;

class LedgerController extends Controller
{
    public function print(Ledger $ledger)
    {
        $account = $ledger->account;
        $transactions = Transaction::where('ledger_id', $ledger->id)->get();
        return view('ledger.print', compact('transactions', 'account', 'ledger'));
    }
}
