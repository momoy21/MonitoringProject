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
        Schema::create('pendapatan_proyek', function (Blueprint $table) {
            $table->integer('norut')->unsigned();
            $table->string('id_project', 10);
            $table->string('no_pendapatan', 9);
            $table->string('no_dokumen', 100);

            $table->string('no_ba', 9)->comment('Referensi ke Berita Acara');
            $table->date('tanggal')->comment('Tanggal input pendapatan');
            $table->date('periode_mulai')->nullable()->comment('Periode mulai pendapatan');
            $table->date('periode_akhir')->nullable()->comment('Periode akhir pendapatan');
            $table->decimal('nilai_pendapatan', 16, 2)->nullable()->comment('Nilai pendapatan');
            $table->string('file_ba', 100)->nullable()->comment('File dokumen berita acara');

            $table->timestamps();

            // Composite primary key: norut, id_project, no_pendapatan, no_dokumen
            $table->primary(['norut', 'id_project', 'no_pendapatan', 'no_dokumen'], 'pendapatan_pk');

            // Foreign key ke berita_acara_project
            $table->foreign(['norut', 'id_project', 'no_ba'], 'fk_pendapatan_ba')
                  ->references(['norut', 'id_project', 'no_ba'])
                  ->on('berita_acara_project')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatan_proyek');
    }
};
