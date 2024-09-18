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
        Schema::create('project_monitorings', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->text('progress_report');
            $table->enum('status', ['on_track', 'at_risk', 'delayed'])->default('on_track');
            $table->decimal('completion_percentage', 5, 2);
            $table->date('report_date');
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_monitorings');
    }
};
