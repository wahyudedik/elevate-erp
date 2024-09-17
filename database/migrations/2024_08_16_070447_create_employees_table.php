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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('employee_code')->unique();  // Kode unik untuk setiap karyawan
            $table->string('email')->unique();
            $table->string('phone')->nullable();  
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id_number')->unique()->nullable();  // Nomor KTP/Paspor
            $table->string('position')->nullable();  // Jabatan
            $table->string('department')->nullable();  // Departemen 
            $table->date('date_of_joining');  // Tanggal bergabung
            $table->decimal('salary', 15, 2)->nullable();  // Gaji pokok
            $table->enum('employment_status', ['permanent', 'contract', 'internship'])->default('permanent');  // Status kerja
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');  // Manager dari karyawan (self-reference)
            $table->string('address')->nullable();  // Alamat
            $table->string('city')->nullable();  // Kota
            $table->string('state')->nullable();  // Provinsi
            $table->string('postal_code')->nullable();  // Kode pos
            $table->string('country')->nullable();  // Negara
            $table->enum('status', ['active', 'inactive', 'terminated', 'resigned'])->default('active');  // Status karyawan
            $table->string('profile_picture')->nullable();  // Foto profil
            $table->string('contract')->nullable();  // contract kerja
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        }); 

        // Tabel untuk riwayat posisi/jabatan
        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->string('position');  // Jabatan
            $table->date('start_date');  // Tanggal mulai jabatan
            $table->date('end_date')->nullable();  // Tanggal berakhir jabatan
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('employee_positions');
    }
};
