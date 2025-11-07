<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kondisiproyek', function (Blueprint $table) {
            $table->string('id_kondisi_proyek', 2)->primary()->comment('ID Kondisi Proyek 2 digit');
            $table->string('desc_kondisi_proyek', 255)->nullable(false)->comment('Nama Kondisi Proyek');
            $table->timestamps();
        });

        DB::table('kondisiproyek')->insert([
            ['id_kondisi_proyek' => 'K1', 'desc_kondisi_proyek' => 'Area office/perkantoran bersih, rapih, beresiko kecil'],
            ['id_kondisi_proyek' => 'K2', 'desc_kondisi_proyek' => 'Area kerja bising, berdebu, kotor lingkungan pabrik, jalan di luar area office beresiko sedang'],
            ['id_kondisi_proyek' => 'K3', 'desc_kondisi_proyek' => 'Area kerja bising, berdebu, kotor lingkungan pabrik, jalan di luar area office beresiko tinggi'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kondisiproyek');
    }
};
