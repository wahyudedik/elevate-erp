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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('report_name');
            $table->enum('report_type', ['balance_sheet', 'income_statement', 'cash_flow']);
            $table->date('report_period_start');
            $table->date('report_period_end');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('balance_sheets', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('financial_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('total_assets', 15, 2);
            $table->decimal('total_liabilities', 15, 2);
            $table->decimal('total_equity', 15, 2);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('income_statements', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('financial_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_expenses', 15, 2);
            $table->decimal('net_income', 15, 2);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('financial_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('operating_cash_flow', 15, 2);
            $table->decimal('investing_cash_flow', 15, 2);
            $table->decimal('financing_cash_flow', 15, 2);
            $table->decimal('net_cash_flow', 15, 2); 
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
        Schema::dropIfExists('balance_sheets');
        Schema::dropIfExists('income_statements');
        Schema::dropIfExists('cash_flows');
    }
};
