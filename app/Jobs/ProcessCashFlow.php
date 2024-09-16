<?php

namespace App\Jobs;

use App\Models\ManagementFinancial\CashFlow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ManagementFinancial\Ledger;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ManagementFinancial\Transaction;

class ProcessCashFlow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cashFlow;

    /**
     * Create a new job instance.
     */
    public function __construct(CashFlow $cashFlow)
    {
        $this->cashFlow = $cashFlow;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createLedgerEntry();
        $this->createTransaction();
    }

    private function createLedgerEntry()
    {
        Ledger::create([
            'account_id' => null, // You may need to set this based on your business logic
            'transaction_date' => now(),
            'transaction_type' => $this->cashFlow->net_cash_flow >= 0 ? 'credit' : 'debit',
            'amount' => abs($this->cashFlow->net_cash_flow),
            'transaction_description' => 'Cash Flow Entry',
        ]);
    }

    private function createTransaction()
    {
        $ledger = Ledger::latest()->first();

        Transaction::create([
            'ledger_id' => $ledger->id,
            'transaction_number' => 'CF' . str_pad($this->cashFlow->id, 8, '0', STR_PAD_LEFT),
            'status' => 'completed',
            'amount' => abs($this->cashFlow->net_cash_flow),
            'notes' => 'operating_cash_flow: ' . $this->cashFlow->operating_cash_flow . ', investing_cash_flow: ' . $this->cashFlow->investing_cash_flow . ', financing_cash_flow: ' . $this->cashFlow->financing_cash_flow . ', financial_report_id: ' . $this->cashFlow->financial_report_id,
        ]);
    }
}
