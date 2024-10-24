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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->date('sale_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps(); 
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_items');
    }
};
