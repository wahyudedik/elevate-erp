<?php

namespace App\Http\Controllers;

use App\Models\ManagementFinancial\CashFlow;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    public function report(CashFlow $cashFlow)
    {
        $financialReport = $cashFlow->financialReport;

        return view('cash-flow.report', compact( 'financialReport',  'cashFlow'));
    }
}
