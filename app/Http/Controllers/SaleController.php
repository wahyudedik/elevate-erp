<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementCRM\Sale;

class SaleController extends Controller
{
    public function printInvoice(Sale $sale)
    {
        // Logic to generate and return the invoice
        // This could involve creating a PDF or a printable HTML view
        return view('sales.invoice', compact('sale'));
    }
}

