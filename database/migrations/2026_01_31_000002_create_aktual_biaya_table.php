<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel Aktual Biaya (AktualBiaya)
     * Menyimpan data aktual biaya per proyek berdasarkan spesifikasi RAB
     * Data diambil dari tabel plsap (SAP Import) dan dimapping via spec_rab_detail
     */
    public function up(): void
    {
        Schema::create('aktual_biaya', function (Blueprint $table) {
            // Cost Center Project - Diambil langsung dari CCProjek SAP
            $table->string('cc_projek', 10)->comment('Cost Center Project dari SAP');
            
            // ID Aktual - Generate otomatis
            // Format: 10 digit (4 digit tahun + 2 digit tanggal + 4 digit nomor urut)
            // Contoh: 2026310001 (Tahun 2026, Tanggal 31, Urut 0001)
            $table->string('id_aktual', 10)->comment('ID Aktual: YYYYDDNNNN');
            
            // ID Spesifikasi RAB - Didapat dari mapping CostElement via spec_rab_detail
            $table->string('id_spec', 10)->comment('ID Spesifikasi RAB dari mapping');
            
            // Tanggal Posting - Diambil langsung dari PostingDate SAP
            $table->date('tanggal_posting')->comment('Tanggal Posting dari SAP');
            
            // Bulan dalam RAB - Diturunkan dari PostingDate SAP menjadi periode bulan
            $table->date('bulan')->comment('Bulan dalam RAB (format tampilan: mmm/yyyy)');
            
            // Nilai dalam RAB - Diambil langsung dari AmountLocal SAP
            $table->decimal('nilai', 18, 2)->nullable()->default(0)->comment('Nilai dari SAP AmountLocal');
            
            // Kategori Biaya - Didapat dari spec_rab berdasarkan id_spec
            // PDP: Pendapatan, HPP: Harga Pokok Penjualan
            $table->string('kategori', 3)->comment('Kategori: PDP/HPP dari spec_rab');
            
            // Reference ke plsap untuk tracking sumber data
            $table->unsignedBigInteger('plsap_id')->nullable()->comment('Reference ke record SAP asli');
            
            $table->timestamps();

            // Composite Primary Key
            $table->primary(['cc_projek', 'id_aktual', 'id_spec']);

            // Foreign Key ke spec_rab
            $table->foreign('id_spec')
                  ->references('id_spec')
                  ->on('spec_rab')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Foreign Key ke plsap (optional - untuk tracking)
            $table->foreign('plsap_id')
                  ->references('id')
                  ->on('plsap')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // Indexes untuk pencarian dan filtering
            $table->index('tanggal_posting');
            $table->index('bulan');
            $table->index('kategori');
            $table->index('cc_projek');
            $table->index(['cc_projek', 'bulan']); // Untuk laporan per proyek per bulan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktual_biaya');
    }
};
