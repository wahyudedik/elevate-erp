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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name'); 
            $table->string('email')->unique();
            $table->string('phone')->nullable(); 
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id_number')->unique()->nullable();  // Nomor KTP/Paspor
            $table->string('position_applied');  // Posisi yang dilamar
            $table->enum('status', ['applied', 'interviewing', 'offered', 'hired', 'rejected'])->default('applied');  // Status rekrutmen
            $table->foreignId('recruiter_id')->nullable()->constrained('employees')->onDelete('set null');  // Rekruter yang menangani
            $table->date('application_date')->default(now());  // Tanggal melamar
            $table->text('resume')->nullable();  // Resume/CV calon karyawan
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->softDeletes();  // Kolom untuk soft delete 
            $table->timestamps();
        });

        // Tabel untuk wawancara
        Schema::create('candidate_interviews', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->onDelete('cascade');
            $table->date('interview_date');  // Tanggal wawancara
            $table->string('interviewer')->nullable();  // Nama pewawancara
            $table->enum('interview_type', ['phone', 'video', 'in_person'])->default('in_person');  // Jenis wawancara
            $table->text('interview_notes')->nullable();  // Catatan wawancara
            $table->enum('result', ['passed', 'failed', 'pending'])->default('pending');  // Hasil wawancara
            $table->softDeletes();  // Kolom untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('candidate_interviews');
    }
};
