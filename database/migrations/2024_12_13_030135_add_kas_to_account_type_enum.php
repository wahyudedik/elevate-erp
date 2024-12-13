<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_type_enum', function (Blueprint $table) {
            DB::statement("ALTER TABLE accounts MODIFY COLUMN account_type ENUM('asset', 'liability', 'equity', 'revenue', 'expense', 'kas')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_type_enum', function (Blueprint $table) {
            DB::statement("ALTER TABLE accounts MODIFY COLUMN account_type ENUM('asset', 'liability', 'equity', 'revenue', 'expense')");
        });
    }
};
