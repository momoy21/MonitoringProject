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
        Schema::create('history_proyek', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Nomor urut untuk history dalam cost center yang sama
            $table->integer('norut')->default(1);

            // Project ID with custom format: YYYY + MM + DDDD
            $table->string('id_project', 10);

            // Cost Center - foreign key relationship to parent data_proyek
            $table->string('cost_center', 9);

            // Dokumen IO - hanya angka
            $table->string('dokumen_io', 9)->nullable();

            // Nama proyek
            $table->text('namaproject');

            // Foreign key relationships
            $table->string('id_konsumen', 6); // FK to konsumen table
            $table->foreign('id_konsumen')->references('id_konsumen')->on('konsumen');

            $table->string('id_datapeluang', 4)->nullable(); // FK to data_peluang table
            $table->foreign('id_datapeluang')->references('id_datapeluang')->on('data_peluang');

            $table->string('id_bidjasa', 2); // FK to bidangjasa table
            $table->foreign('id_bidjasa')->references('id_bidjasa')->on('bidangjasa');

            // Lokasi proyek
            $table->string('lokasi_proyek', 100)->nullable();

            // Jarak lokasi dengan pilihan 1-6
            $table->integer('jarak_lokasi')->nullable();

            // FK to kondisiproyek table
            $table->string('id_kondisi_proyek', 2);
            $table->foreign('id_kondisi_proyek')->references('id_kondisi_proyek')->on('kondisiproyek');

            // Nomor kontrak
            $table->string('no_kontrak', 100)->nullable();

            // Tanggal fields
            $table->date('tgl_pengakuan')->nullable();
            $table->date('tgl_kontrak')->nullable();
            $table->date('start_kontrak');
            $table->date('finish_kontrak');
            $table->date('tgl_expire')->nullable();

            // Penanggung jawab - FK to master_manager
            $table->string('penanggung_jawab', 7)->nullable();
            $table->foreign('penanggung_jawab')->references('nik')->on('master_manager');

            // Nilai proyek dalam format Rupiah
            $table->decimal('nilai_proyek', 16, 2)->nullable();

            // Status: O=Open, I=InProgress, C=Close, P=Pending, F=Finish Pekerjaan
            $table->char('status', 1)->default('O');

            // Keterangan: 1=Kontrak Induk, 2=Bukan Kontrak Induk
            $table->char('keterangan', 1)->nullable();

            // Path untuk upload dokumen
            $table->string('dokumen_path')->nullable();

            $table->timestamps();

            // Create index on cost_center for grouping
            $table->index('cost_center');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_proyek');
    }
};
