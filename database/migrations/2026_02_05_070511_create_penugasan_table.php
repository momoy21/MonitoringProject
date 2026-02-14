<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penugasan', function (Blueprint $table) {

            // PRIMARY KEY TEKNIS (WAJIB)
            $table->bigIncrements('id');

            // BUSINESS KEY (BPS)
            $table->string('IDPenugasan', 10); // YYYYMMDDXX
            $table->string('cost_center', 9);
            $table->integer('Norut');

            $table->string('NIK', 9);
            $table->string('NoSurat', 50)->nullable();
            $table->string('Dokumen_IO', 9)->nullable();
            $table->string('Jabatan', 30);

            $table->date('Periodeawal');
            $table->date('Periodeakhir');
            $table->integer('Bobot'); // persen

            $table->char('Status', 1)->default('A');
            $table->text('Keterangan')->nullable();

            $table->timestamps();

            // =============================
            // CONSTRAINT BPS
            // =============================

            // 1 IDPenugasan boleh banyak row, tapi Norut unik
            $table->unique(['IDPenugasan', 'Norut']);

            // (Opsional tapi bagus)
            // $table->foreign('NIK')->references('NIK')->on('karyawan');
            // $table->foreign('cost_center')->references('cost_center')->on('history_proyek');
        });
    }

    public function down()
    {
        Schema::dropIfExists('penugasan');
    }
};