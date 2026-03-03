<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah struktur tabel penugasan:
     * - Menghapus kolom 'id' (auto-increment)
     * - Membuat composite primary key dari: IDPenugasan, cost_center, Norut, NIK
     */
    public function up(): void
    {
        // 1. Drop the existing unique constraint if exists
        $indexExists = DB::select("SHOW INDEX FROM penugasan WHERE Key_name = 'penugasan_idpenugasan_norut_unique'");
        if (!empty($indexExists)) {
            Schema::table('penugasan', function (Blueprint $table) {
                $table->dropUnique(['IDPenugasan', 'Norut']);
            });
        }

        // 2. Check if 'id' column exists before dropping
        $idColumnExists = Schema::hasColumn('penugasan', 'id');
        if ($idColumnExists) {
            // For MySQL: Drop primary key first, then drop the column
            // MySQL requires dropping auto_increment before dropping primary key
            DB::statement('ALTER TABLE penugasan MODIFY id BIGINT NOT NULL');
            DB::statement('ALTER TABLE penugasan DROP PRIMARY KEY');
            DB::statement('ALTER TABLE penugasan DROP COLUMN id');
        }

        // 3. Add composite primary key
        Schema::table('penugasan', function (Blueprint $table) {
            $table->primary(['IDPenugasan', 'cost_center', 'Norut', 'NIK']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove composite primary key
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropPrimary(['IDPenugasan', 'cost_center', 'Norut', 'NIK']);
        });

        // Add back id column as primary key
        Schema::table('penugasan', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        // Re-add the unique constraint
        Schema::table('penugasan', function (Blueprint $table) {
            $table->unique(['IDPenugasan', 'Norut']);
        });
    }
};
