<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\SupplierTransactionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sales/{sale}/print-invoice', [SaleController::class, 'printInvoice'])->name('sale.print-invoice');


Route::get('/supplier-transactions/{supplierTransaction}/print', [SupplierTransactionController::class, 'print'])
    ->name('supplier-transactions.print');


Route::get('/sales-transaction/{salesTransaction}/print-invoice', [SalesTransactionController::class, 'printInvoice'])
    ->name('sales-transaction.print-invoice');

Route::get('/apply/{recruitment}', [CandidateController::class, 'showApplicationForm'])
    ->name('candidate.apply');
Route::post('/apply/{recruitment}/', [CandidateController::class, 'submitApplication'])
    ->name('candidate.submit');

Route::get('journal-entries/{journalEntry}/print', [JournalEntryController::class, 'print'])->name('journal-entries.print');

// Email verification routes
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->to('/admin/login');
})->middleware(['signed'])->name('verification.verify');


// New Artisan routes for migration and seeding
Route::get('/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return 'Database migration completed successfully.';
});

Route::get('/seed', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return 'Database seeding completed successfully.';
});

Route::get('/optimize', function () {
    Artisan::call('optimize', ['--force' => true]);
    return 'Application optimized successfully.';
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link', ['--force' => true]);
    return 'Storage link created successfully.';
});

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear', ['--force' => true]);
    return 'Cached data cleared successfully.';
});
