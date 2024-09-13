<?php

namespace App\Http\Controllers;

use App\Models\ManagementStock\SupplierTransactions;
use Illuminate\Http\Request;

class SupplierTransactionController extends Controller
{
    public function print(SupplierTransactions $supplierTransaction)
    {
        // Your print logic here
        // For example, you might return a view with the transaction details
        return view('supplier-transactions.print', compact('supplierTransaction'));
    }
}
