<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class SalesTransactionController extends Controller
{
    public function printInvoice(SalesTransaction $salesTransaction)
    {
        $salesTransaction->load('salesItem');

        if ($salesTransaction->salesItem->isEmpty()) {
            // Handle case where there are no sales items
            return back()->with('error', 'No sales items found for this transaction.');
        }

        return view('invoices.print', compact('salesTransaction'));
    }
}
