<?php

namespace App\Http\Controllers;

use App\Models\ClientDev;
use App\Models\ContactDev;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function landingPage()
    {
        $clients = ClientDev::get();
        $contactUs = ContactDev::first();

        return view('welcome', compact('clients', 'contactUs'));
    }

    public function newsLetterStore(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email'
        ]);

        $existingNewsletter = Newsletter::where('email', $validated['email'])->first();

        if ($existingNewsletter) {
            return back()->with('error', 'Email already subscribed to our newsletter.');
        } else {
            Newsletter::create($validated);
            return back()->with('success', 'Thank you for subscribing to our newsletter!');
        }
    }
}
