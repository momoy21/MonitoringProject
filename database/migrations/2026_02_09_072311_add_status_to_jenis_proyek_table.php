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
        Schema::table('jenis_proyek', function (Blueprint $table) {
            // Menambah kolom status setelah nama_jenis
            $table->enum('status', ['A', 'N'])->default('A')->after('nama_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_proyek', function (Blueprint $table) {
            // Menghapus kolom status jika rollback
            $table->dropColumn('status');
        });
    }
};
