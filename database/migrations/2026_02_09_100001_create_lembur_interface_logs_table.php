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
        Schema::create('lembur_interface_logs', function (Blueprint $table) {
            $table->id();
            
            // Log info
            $table->date('periode_awal')->comment('Periode awal filter');
            $table->date('periode_akhir')->comment('Periode akhir filter');
            $table->string('filename')->nullable()->comment('Nama file CSV yang dihasilkan');
            $table->integer('total_records')->default(0)->comment('Jumlah record yang diproses');
            
            // Status
            $table->enum('status', ['success', 'failed'])->comment('Status eksekusi: success/failed');
            $table->text('message')->nullable()->comment('Pesan detail');
            $table->text('error_detail')->nullable()->comment('Detail error jika gagal');
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable()->comment('User yang menjalankan');
            $table->string('ip_address', 45)->nullable()->comment('IP address user');
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index(['periode_awal', 'periode_akhir']);
            
            // Foreign key ke users
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur_interface_logs');
    }
};
