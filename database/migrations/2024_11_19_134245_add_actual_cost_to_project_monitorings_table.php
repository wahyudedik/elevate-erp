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
        Schema::table('project_monitorings', function (Blueprint $table) {
            $table->decimal('actual_cost', 15, 2)->after('completion_percentage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table) {
            $table->dropColumn('actual_cost');
        });
    }
};
