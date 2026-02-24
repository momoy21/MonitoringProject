<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk menyimpan notifikasi kontrak habis
     */
    public function up(): void
    {
        Schema::create('kontrak_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('id_project', 10);
            $table->unsignedBigInteger('user_id');
            $table->string('type', 20)->default('expired'); // 'expired', 'expiring'
            $table->string('title');
            $table->text('message');
            $table->string('no_kontrak', 100)->nullable();
            $table->date('finish_kontrak')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['id_project', 'user_id', 'type']);
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_project')->references('id_project')->on('data_proyek')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak_notifications');
    }
};
