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
        Schema::table('rab_proyek', function (Blueprint $table) {
            $table->string('jenis_proyek', 2)->nullable()->after('divisi');

            // Add foreign key
            $table->foreign('jenis_proyek')
                  ->references('kode_jenis')
                  ->on('jenis_proyek')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rab_proyek', function (Blueprint $table) {
            $table->dropForeign(['jenis_proyek']);
            $table->dropColumn('jenis_proyek');
        });
    }
};
