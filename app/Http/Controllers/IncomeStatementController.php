<?php

namespace App\Http\Controllers;

use App\Models\ManagementFinancial\IncomeStatement;
use Illuminate\Http\Request;

class IncomeStatementController extends Controller
{
    public function report(IncomeStatement $incomeStatement)
    {
        $financialReport = $incomeStatement->financialReport;

        // Retrieve additional financial data
        $cashFlow = $financialReport->cashFlow;

        return view('income-statement.report', compact( 'financialReport', 'incomeStatement', 'cashFlow'));
    }
}
