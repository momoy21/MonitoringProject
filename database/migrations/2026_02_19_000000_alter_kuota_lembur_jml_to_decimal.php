<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom jml_wd, jml_we, jml_hn dari integer ke decimal
     * agar bisa menyimpan nilai desimal (misal 5.1, 2.5, dll).
     */
    public function up(): void
    {
        Schema::table('kuota_lembur', function (Blueprint $table) {
            $table->decimal('jml_wd', 8, 2)->default(0)->comment('Jumlah Rencana Lembur Weekday')->change();
            $table->decimal('jml_we', 8, 2)->default(0)->comment('Jumlah Rencana Lembur Weekend')->change();
            $table->decimal('jml_hn', 8, 2)->default(0)->comment('Jumlah Rencana Lembur Hari Libur Nasional/Kalender')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuota_lembur', function (Blueprint $table) {
            $table->integer('jml_wd')->default(0)->comment('Jumlah Rencana Lembur Weekday')->change();
            $table->integer('jml_we')->default(0)->comment('Jumlah Rencana Lembur Weekend')->change();
            $table->integer('jml_hn')->default(0)->comment('Jumlah Rencana Lembur Hari Libur Nasional/Kalender')->change();
        });
    }
};
