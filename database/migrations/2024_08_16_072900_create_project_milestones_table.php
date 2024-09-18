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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->string('milestone_name');
            $table->text('milestone_description')->nullable();
            $table->date('milestone_date');
            $table->enum('status', ['pending', 'achieved'])->default('pending');
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });

        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->string('resource_name');
            $table->enum('resource_type', ['human', 'material', 'financial']);
            $table->decimal('resource_cost', 15, 2)->nullable();
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_resources');
    }
};
