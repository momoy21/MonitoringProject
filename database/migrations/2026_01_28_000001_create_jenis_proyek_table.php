<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_proyek', function (Blueprint $table) {
            $table->string('kode_jenis', 2)->primary();
            $table->string('nama_jenis', 50);
            $table->enum('status', ['A', 'N'])->default('A');
            $table->timestamps();
        });

        DB::table('jenis_proyek')->insert([
            [
                'kode_jenis' => 'P1',
                'nama_jenis' => 'Implementasi',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_jenis' => 'P2',
                'nama_jenis' => 'Sewa / Support',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_jenis' => 'P3',
                'nama_jenis' => 'Trading (Jual Putus)',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_proyek');
    }
};
