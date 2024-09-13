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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('supplier_code')->unique();  // Kode unik pemasok
            $table->string('contact_name')->nullable();  // Nama kontak di perusahaan pemasok
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable(); 
            $table->string('website')->nullable();
            $table->string('tax_identification_number')->nullable();  // Nomor NPWP atau pajak lainnya
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');  // Status pemasok
            $table->decimal('credit_limit', 15, 2)->nullable();  // Batas kredit untuk pemasok
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });

        // Tabel untuk riwayat transaksi dengan pemasok
        Schema::create('supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->string('transaction_code')->unique();  // Kode transaksi
            $table->enum('transaction_type', ['purchase_order', 'payment', 'refund']);  // Jenis transaksi
            $table->decimal('amount', 15, 2);  // Jumlah transaksi
            $table->date('transaction_date');  // Tanggal transaksi
            $table->text('notes')->nullable();  // Catatan tambahan mengenai transaksi
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('supplier_transactions');
    }
};
