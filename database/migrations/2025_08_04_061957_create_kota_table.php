<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provinsi_id')->constrained('provinsi')->onDelete('cascade');
            $table->string('nama', 100);
            $table->string('kode_pos', 10)->nullable();
            $table->timestamps();

            $table->index(['nama']);
            $table->index(['provinsi_id', 'nama']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kota');
    }
};
