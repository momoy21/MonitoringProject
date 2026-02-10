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
        Schema::create('kuota_lembur', function (Blueprint $table) {
            // Composite primary key: cost_center + dok_io + nik + bulan
            $table->string('cost_center', 9)->comment('Cost Center - Relasi ke tabel proyek');
            $table->string('dok_io', 9)->comment('Dokumen IO - Relasi ke tabel proyek');
            $table->string('nik', 9)->comment('NIK - Relasi ke tabel Karyawan');
            $table->integer('bulan')->comment('Bulan - Otomatis dimulai dari 1, setiap ganti NIK maka dimulai dari 1');
            
            // Periode fields
            $table->date('periode_awal')->comment('Mulai periode lembur');
            $table->date('periode_akhir')->comment('Akhir periode lembur');
            
            // Jumlah rencana lembur
            $table->integer('jml_wd')->default(0)->comment('Jumlah Rencana Lembur Weekday');
            $table->integer('jml_we')->default(0)->comment('Jumlah Rencana Lembur Weekend');
            $table->integer('jml_hn')->default(0)->comment('Jumlah Rencana Lembur Hari Libur Nasional/Kalender');
            
            // Status
            $table->char('status', 1)->nullable()->comment('Status: NULL=belum terkirim, F=sudah terkirim ke path');
            
            $table->timestamps();
            
            // Composite primary key
            $table->primary(['cost_center', 'dok_io', 'nik', 'bulan']);
            
            // Indexes
            $table->index('nik');
            $table->index('status');
            $table->index('periode_awal');
            $table->index('periode_akhir');
            $table->index(['periode_awal', 'periode_akhir', 'status']);
            
            // Foreign key ke tabel karyawan
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_lembur');
    }
};
