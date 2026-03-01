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
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->string('id_project', 10)->default('')->after('cost_center');
            $table->integer('no_urut')->default(0)->after('id_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->dropColumn(['id_project', 'no_urut']);
        });
    }
};
