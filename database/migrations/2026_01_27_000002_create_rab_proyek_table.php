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
        Schema::create('rab_proyek', function (Blueprint $table) {
            // Primary Key: Nopengajuan
            // Format: YYYYMMNN (4 digit tahun + 2 digit bulan + 2 digit nomor urut)
            $table->string('nopengajuan', 8)->primary()->comment('Nomor Pengajuan: YYYYMMNN');
            
            // Dokumen IO
            $table->string('dokumen_io', 9)->nullable()->comment('Dokumen IO');
            
            // Cost Center
            $table->string('cost_center', 9)->comment('Cost Center');
            
            // Nama Proyek
            $table->text('nama_project')->comment('Nama Proyek');
            
            // ID Konsumen - relasi ke tabel konsumen
            $table->string('id_konsumen', 6)->comment('ID Konsumen - relasi ke tabel konsumen');
            
            // Bidang Jasa - relasi ke tabel bidangjasa
            $table->string('id_bidjasa', 2)->comment('ID Bidang Jasa - relasi ke tabel bidangjasa');
            
            // Project Manager
            $table->string('pm', 100)->nullable()->comment('Project Manager');
            
            // Divisi - relasi ke tabel master_divisi
            $table->string('divisi', 10)->nullable()->comment('Kode Divisi - relasi ke tabel master_divisi');
            
            // Nilai Proyek
            $table->decimal('nilai_proyek', 16, 2)->nullable()->comment('Nilai Proyek');
            
            // Tanggal Input
            $table->date('tgl_input')->comment('Tanggal Pengajuan');
            
            // Keterangan RAB: P=Pleno, T=Tidak Pleno, R=Revisi RAB
            $table->char('keterangan', 1)->nullable()->comment('Keterangan: P=Pleno, T=Tidak Pleno, R=Revisi RAB');
            
            // Progress RAB: 01=Dokumen belum diterima, 02=Proses tanda tangan BOD, 03=Revisi RAB, 04=Done
            $table->string('progress', 2)->nullable()->comment('Progress: 01=Dokumen belum diterima, 02=Proses TTD BOD, 03=Revisi RAB, 04=Done');
            
            // Hasil Pleno: TT=Tidak Tercapai RKAP, TR=Tercapai RKAP
            $table->string('hasil_pleno', 2)->nullable()->comment('Hasil Pleno: TT=Tidak Tercapai RKAP, TR=Tercapai RKAP');
            
            // Catatan Hasil Pleno
            $table->text('catatan')->nullable()->comment('Catatan Hasil Pleno');
            
            // Margin RKAP
            $table->decimal('margin_rkap', 16, 2)->nullable()->comment('Margin RKAP');
            
            // Margin Pleno
            $table->decimal('margin_pleno', 16, 2)->nullable()->comment('Margin Pleno');
            
            // File Uploads - path storage
            $table->text('rab_upload')->nullable()->comment('Path file RAB (Excel)');
            $table->text('file_upload')->nullable()->comment('Path file Kontrak/JO/PO/SPK (PDF)');
            $table->text('peta_risk_upload')->nullable()->comment('Path file Peta Risiko (PDF)');
            $table->text('hasil_upload')->nullable()->comment('Path file Hasil Pleno (PDF)');
            
            $table->timestamps();
            
            // Indexes
            $table->index('cost_center');
            $table->index('id_konsumen');
            $table->index('id_bidjasa');
            $table->index('divisi');
            $table->index('tgl_input');
            $table->index('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_proyek');
    }
};
