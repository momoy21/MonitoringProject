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
        // Add kode_divisi to data_proyek table
        Schema::table('data_proyek', function (Blueprint $table) {
            $table->string('kode_divisi', 10)->nullable()->after('penanggung_jawab')->comment('Kode Divisi dari master_divisi');
            $table->foreign('kode_divisi')->references('kode_divisi')->on('master_divisi')->onDelete('set null');
            $table->index('kode_divisi');
        });

        // Add kode_divisi to history_proyek table
        Schema::table('history_proyek', function (Blueprint $table) {
            $table->string('kode_divisi', 10)->nullable()->after('penanggung_jawab')->comment('Kode Divisi dari master_divisi');
            $table->foreign('kode_divisi')->references('kode_divisi')->on('master_divisi')->onDelete('set null');
            $table->index('kode_divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_proyek', function (Blueprint $table) {
            $table->dropForeign(['kode_divisi']);
            $table->dropIndex(['kode_divisi']);
            $table->dropColumn('kode_divisi');
        });

        Schema::table('history_proyek', function (Blueprint $table) {
            $table->dropForeign(['kode_divisi']);
            $table->dropIndex(['kode_divisi']);
            $table->dropColumn('kode_divisi');
        });
    }
};
