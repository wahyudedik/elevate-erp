<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagementFinancial\JournalEntry;

class JournalEntryController extends Controller
{
    public function print(JournalEntry $journalEntry)
    {
        $account = $journalEntry->account;
        return view('journal-entries.print', compact('journalEntry', 'account'));
    }
}
