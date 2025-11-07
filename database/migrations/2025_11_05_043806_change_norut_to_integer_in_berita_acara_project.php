<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key constraint first if exists
        Schema::table('berita_acara_project', function (Blueprint $table) {
            $table->dropPrimary(['norut', 'id_project', 'no_ba']);
        });

        // Update existing data to remove leading zeros
        DB::statement("UPDATE berita_acara_project SET norut = CAST(norut AS UNSIGNED)");

        // Change column type to integer
        Schema::table('berita_acara_project', function (Blueprint $table) {
            $table->integer('norut')->unsigned()->change()->comment('Auto from history_proyek.norut');

            // Recreate primary key
            $table->primary(['norut', 'id_project', 'no_ba']);
        });
    }

    public function down(): void
    {
        Schema::table('berita_acara_project', function (Blueprint $table) {
            $table->dropPrimary(['norut', 'id_project', 'no_ba']);
            $table->string('norut', 2)->change();
            $table->primary(['norut', 'id_project', 'no_ba']);
        });

        // Pad with zeros again
        DB::statement("UPDATE berita_acara_project SET norut = LPAD(norut, 2, '0')");
    }
};
