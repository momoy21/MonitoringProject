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
        Schema::create('spec_rab', function (Blueprint $table) {
            $table->string('idspec', 4)->primary()->comment('Format: 0001, 0002, dst');
            $table->string('spec_rab', 100)->nullable(false)->comment('Spesifikasi RAB');
            $table->string('norutspec', 2)->nullable(false)->comment('Nomor urut untuk pengurutan tampilan');
            $table->string('kategori', 3)->nullable(false)->comment('PDP: Pendapatan, HPP: Harga Pokok Penjualan');
            $table->timestamps();

            // Index untuk performa sorting berdasarkan idspec
            $table->index(['idspec', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spec_rab');
    }
};
