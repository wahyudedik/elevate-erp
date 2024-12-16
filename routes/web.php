<?php

use App\Livewire\PresentCheck;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\LedgerController;
use App\Http\Middleware\ApplyTenantScopes;
use Filament\Http\Middleware\Authenticate;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\SupplierTransactionController;

// route present check
Route::get('/present-check', PresentCheck::class)
    ->name('present-check')
    ->middleware([
        Authenticate::class . ':admin',
        ApplyTenantScopes::class
    ]);


Route::group(['middleware' => 'auth'], function () {
    // present check

    //sales
    Route::get('/sales/{sale}/print-invoice', [SaleController::class, 'printInvoice'])
        ->name('sale.print-invoice');

    // print supplier transaction
    Route::get('/supplier-transactions/{supplierTransaction}/print', [SupplierTransactionController::class, 'print'])
        ->name('supplier-transactions.print');
});

// route redirect admin
// Route::get('/login', function () {
//     return redirect('admin/login');
// })->name('login-page');

Route::get('/', [LandingPageController::class, 'landingPage'])
    ->name('landingPage');

Route::post('/newsletter', [LandingPageController::class, 'newsLetterStore'])
    ->name('newsletter.store');

// print candidate application dan poster
Route::get('/apply/{recruitment}', [CandidateController::class, 'showApplicationForm'])
    ->name('candidate.apply');
Route::post('/apply/{recruitment}/', [CandidateController::class, 'submitApplication'])
    ->name('candidate.submit');
Route::get('/recruitment/{recruitment}/generate-poster/{format?}', [CandidateController::class, 'generatePoster'])
    ->name('recruitment.generate-poster')
    ->where('format', 'pdf');
