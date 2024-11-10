<?php

namespace App\Http\Controllers;

use App\Models\ClientDev;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function client()
    {
        $clients = ClientDev::get();

        return view('welcome', compact('clients'));
    }
}
