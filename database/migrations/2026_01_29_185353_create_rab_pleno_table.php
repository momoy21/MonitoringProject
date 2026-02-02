<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rab_pleno', function (Blueprint $table) {
            // Menggunakan nopengajuan sebagai Primary Key (String)
            $table->string('nopengajuan', 20)->primary(); 
            $table->date('tglinput');
            $table->string('cost_center', 15);
            $table->string('namaproject', 255);
            $table->unsignedBigInteger('idkonsumen');
            $table->decimal('nilaiproyek', 17, 2);
            $table->decimal('marginrkap', 5, 2);
            $table->decimal('marginpleno', 5, 2)->default(0);
            $table->enum('keterangan', ['P', 'T', 'R'])->comment('P=Pleno, T=Tidak, R=Revisi');
            $table->string('progress', 5); // 01, 02, 04
            $table->enum('hasil_pleno', ['TR', 'TT'])->nullable();
            $table->text('catatan')->nullable();
            $table->string('hasilupload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rab_pleno');
    }
};