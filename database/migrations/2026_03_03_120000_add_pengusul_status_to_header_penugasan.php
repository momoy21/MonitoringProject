<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field:
     * - Pengusul: Nama yang mengajukan surat penugasan
     * - Status: P = Pengajuan, A = Approve
     */
    public function up(): void
    {
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->string('Pengusul', 100)->nullable()->after('PejabatTandatangan');
            $table->char('Status', 1)->default('P')->after('Pengusul')->comment('P=Pengajuan, A=Approve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->dropColumn(['Pengusul', 'Status']);
        });
    }
};
