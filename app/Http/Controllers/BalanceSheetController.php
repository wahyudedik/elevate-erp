<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementFinancial\BalanceSheet;
use App\Models\ManagementFinancial\FinancialReport;

class BalanceSheetController extends Controller
{
    public function report(BalanceSheet $balanceSheet)
    {
        $financialReport = $balanceSheet->financialReport;

        return view('balance-sheet.report', compact('balanceSheet', 'financialReport'));
    }
}
