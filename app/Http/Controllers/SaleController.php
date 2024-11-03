<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementCRM\Sale;
use App\Models\ManagementCRM\SaleItem;

class SaleController extends Controller
{
    public function printInvoice(Sale $sale)
    {
        $saleItems = SaleItem::where('sale_id', $sale->id)->get();
        return view('sales.invoice', compact('sale', 'saleItems'));
    }
}
