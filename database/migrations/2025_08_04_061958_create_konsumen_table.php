<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('konsumen', function (Blueprint $table) {
            $table->string('id_konsumen', 6)->primary();
            $table->string('konsumen', 150);
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->onDelete('set null');
            $table->foreignId('kota_id')->nullable()->constrained('kota')->onDelete('set null');
            $table->string('alamat1', 255)->nullable();
            $table->string('alamat2', 255)->nullable();
            $table->string('kode_pos', 5)->nullable();
            $table->string('telp_kantor', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email', 70)->nullable();
            $table->timestamps();

            $table->index(['konsumen']);
            $table->index(['email']);
            $table->index(['provinsi_id']);
            $table->index(['kota_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('konsumen');
    }
};
