<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecRabDetailSeeder extends Seeder
{
    /**
     * Seed data mapping Cost Element ke Spesifikasi RAB
     * 
     * Data official mapping berdasarkan dokumen resmi.
     * Mapping berdasarkan:
     * - id_spec dari tabel spec_rab (format 4 digit: 0001, 0002, dst)
     * - cost_element dari data SAP (plsap)
     */
    public function run(): void
    {
        $mappings = [
            // ===============================================
            // 1. Biaya Tenaga Proyek (id_spec: 0001)
            // ===============================================
            ['id_spec' => '0001', 'cost_element' => '5001101', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '5101146', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '5101147', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001101', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001103', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001104', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001111', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001118', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001119', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001121', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001122', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001123', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001134', 'description_ce' => ''],
            ['id_spec' => '0001', 'cost_element' => '6001141', 'description_ce' => ''],

            // ===============================================
            // 2. Biaya Tunjangan Proyek (id_spec: 0002)
            // ===============================================
            ['id_spec' => '0002', 'cost_element' => '6001102', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001105', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001106', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001108', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001109', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001110', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001112', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001115', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001117', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001127', 'description_ce' => ''],
            ['id_spec' => '0002', 'cost_element' => '6001129', 'description_ce' => ''],

            // ===============================================
            // 3. Biaya Lembur (id_spec: 0003)
            // ===============================================
            ['id_spec' => '0003', 'cost_element' => '6001120', 'description_ce' => ''],

            // ===============================================
            // 4. Biaya Material & Suku Cadang (id_spec: 0004)
            // ===============================================
            ['id_spec' => '0004', 'cost_element' => '5101115', 'description_ce' => ''],
            ['id_spec' => '0004', 'cost_element' => '5101151', 'description_ce' => ''],

            // ===============================================
            // 5. Biaya Sub Kontraktor/Konsultan (Maint) (id_spec: 0005)
            // ===============================================
            ['id_spec' => '0005', 'cost_element' => '5107101', 'description_ce' => ''],
            ['id_spec' => '0005', 'cost_element' => '5107102', 'description_ce' => ''],
            ['id_spec' => '0005', 'cost_element' => '6010102', 'description_ce' => ''],
            ['id_spec' => '0005', 'cost_element' => '6011101', 'description_ce' => ''],

            // ===============================================
            // 7. Biaya Transportasi & SPD (id_spec: 0007)
            // ===============================================
            ['id_spec' => '0007', 'cost_element' => '5001107', 'description_ce' => ''],
            ['id_spec' => '0007', 'cost_element' => '5102112', 'description_ce' => ''],
            ['id_spec' => '0007', 'cost_element' => '6007101', 'description_ce' => ''],
            ['id_spec' => '0007', 'cost_element' => '6007201', 'description_ce' => ''],

            // ===============================================
            // 8. Biaya Bunga & Asuransi Proyek (id_spec: 0008)
            // ===============================================
            ['id_spec' => '0008', 'cost_element' => '5105110', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '5105111', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '6010106', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '7003104', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '7004105', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '7007103', 'description_ce' => ''],
            ['id_spec' => '0008', 'cost_element' => '7007104', 'description_ce' => ''],

            // ===============================================
            // 9. Biaya Depresiasi Asset (id_spec: 0009)
            // ===============================================
            ['id_spec' => '0009', 'cost_element' => '6008105', 'description_ce' => ''],
            ['id_spec' => '0009', 'cost_element' => '6008106', 'description_ce' => ''],
            ['id_spec' => '0009', 'cost_element' => '6008111', 'description_ce' => ''],
            ['id_spec' => '0009', 'cost_element' => '6008127', 'description_ce' => ''],
            ['id_spec' => '0009', 'cost_element' => '6008128', 'description_ce' => ''],
            ['id_spec' => '0009', 'cost_element' => '6008144', 'description_ce' => ''],

            // ===============================================
            // 10. Biaya Komunikasi (id_spec: 0010)
            // ===============================================
            ['id_spec' => '0010', 'cost_element' => '5101126', 'description_ce' => ''],
            ['id_spec' => '0010', 'cost_element' => '6006101', 'description_ce' => ''],

            // ===============================================
            // 11. Biaya Pelatihan (id_spec: 0011)
            // ===============================================
            ['id_spec' => '0011', 'cost_element' => '5001108', 'description_ce' => ''],
            ['id_spec' => '0011', 'cost_element' => '6013101', 'description_ce' => ''],

            // ===============================================
            // 12. Biaya Kantor & Umum Proyek (Adm) (id_spec: 0012)
            // ===============================================
            ['id_spec' => '0012', 'cost_element' => '6003110', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '5101148', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '5107104', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6002101', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6002102', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6003101', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6011102', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6011105', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6011107', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6011115', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6012104', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6013117', 'description_ce' => ''],
            ['id_spec' => '0012', 'cost_element' => '6001113', 'description_ce' => 'Ekstra Food'],

            // ===============================================
            // 13. Biaya Kontijensi & Relasi Proyek (id_spec: 0013)
            // ===============================================
            ['id_spec' => '0013', 'cost_element' => '6004101', 'description_ce' => ''],
            ['id_spec' => '0013', 'cost_element' => '6011109', 'description_ce' => ''],
            ['id_spec' => '0013', 'cost_element' => '6012101', 'description_ce' => ''],
        ];

        $now = now();

        // Clear existing data first (use delete to avoid FK issues)
        DB::table('spec_rab_detail')->delete();

        foreach ($mappings as $mapping) {
            // Pad cost_element to 10 digits with leading zeros (SAP format)
            $costElement = str_pad($mapping['cost_element'], 10, '0', STR_PAD_LEFT);
            
            DB::table('spec_rab_detail')->insert([
                'id_spec' => $mapping['id_spec'],
                'cost_element' => $costElement,
                'description_ce' => $mapping['description_ce'],
                'status' => 'A', // Default aktif
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('✓ Seeded ' . count($mappings) . ' spec_rab_detail records (Official Data)');
    }
}
