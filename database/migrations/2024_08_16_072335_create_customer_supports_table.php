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
        Schema::create('customer_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->decimal('customer_rating', 2, 1)->nullable(); // Rating from 1.0 to 5.0
            $table->enum('customer_satisfaction', ['satisfied', 'neutral', 'dissatisfied'])->nullable();
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });

        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('customer_supports')->onDelete('cascade');
            $table->text('response');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_supports');
        Schema::dropIfExists('ticket_responses');
    }
};
