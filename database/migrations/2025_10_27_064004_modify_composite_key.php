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
        // Get the primary key name
        $primaryKeyName = $this->getPrimaryKeyName();

        if ($primaryKeyName) {
            // Drop existing primary key using raw SQL
            DB::statement("ALTER TABLE detail_rab DROP PRIMARY KEY");
        }

        // Drop id column if exists
        if (Schema::hasColumn('detail_rab', 'id')) {
            Schema::table('detail_rab', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        // Add composite primary key
        DB::statement("ALTER TABLE detail_rab ADD PRIMARY KEY (id_rab, id_detail_rab)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop composite primary key
        DB::statement("ALTER TABLE detail_rab DROP PRIMARY KEY");

        // Add auto-increment id column
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->id()->first();
        });
    }

    /**
     * Get the primary key name
     */
    private function getPrimaryKeyName()
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'detail_rab'
            AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ");

        return $result[0]->CONSTRAINT_NAME ?? null;
    }
};
