<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementFinancial\FinancialReport;

class FinancialReportController extends Controller
{
    public function report(FinancialReport $financialReport)
    {
        $financialReport = FinancialReport::with('balanceSheet', 'incomeStatement', 'cashFlow')
        ->find($financialReport->id);

        return view('financial-report.report', compact('financialReport'));
    }
}
