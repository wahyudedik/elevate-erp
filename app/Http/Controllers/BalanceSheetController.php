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

        // Retrieve additional financial data
        $incomeStatement = $financialReport->incomeStatement;
        $cashFlow = $financialReport->cashFlow;

        return view('balance-sheet.report', compact('balanceSheet', 'financialReport', 'incomeStatement', 'cashFlow'));
    }
}
