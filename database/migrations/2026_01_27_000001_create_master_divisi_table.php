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
        Schema::create('master_divisi', function (Blueprint $table) {
            $table->string('kode_divisi', 10)->primary()->comment('Kode Divisi (DT, ERP, Infra)');
            $table->string('nama_divisi', 100)->comment('Nama Divisi');
            $table->char('status', 1)->default('A')->comment('Status: A=Aktif, N=Non Aktif');
            $table->timestamps();
        });

        // Insert default data
        DB::table('master_divisi')->insert([
            [
                'kode_divisi' => 'DT',
                'nama_divisi' => 'Digital Transformation',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_divisi' => 'ERP',
                'nama_divisi' => 'ERP',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_divisi' => 'Infra',
                'nama_divisi' => 'Infrastruktur',
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_divisi');
    }
};
