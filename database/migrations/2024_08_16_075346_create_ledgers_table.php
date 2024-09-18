<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /** 
     * Run the migrations. 
     */
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->text('transaction_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('cascade');
            $table->string('transaction_number')->unique();
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('transactions');
    }
};
