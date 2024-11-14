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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('job_title');
            $table->text('job_description');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship']);
            $table->string('location'); 
            $table->date('posted_date');
            $table->date('closing_date')->nullable(); 
            $table->string('status')->default('open');
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps(); 
        });

        Schema::create('applications', function (Blueprint $table) { 
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('recruitment_id')->nullable()->constrained('recruitments')->onDelete('cascade');
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->onDelete('cascade');
            $table->enum('status', ['applied', 'interviewing', 'offered', 'hired', 'rejected'])->default('applied');
            $table->text('resume')->nullable();
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
        Schema::dropIfExists('applications');
    }
};
