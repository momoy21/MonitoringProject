<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpesifikasiRABSeeder extends Seeder
{
    public function run(): void
    {
        $spesifikasiRAB = [
            [
                'id_spec' => '0001',
                'spec_rab' => 'Biaya Tenaga Proyek',
                'norutspec' => '02',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0002',
                'spec_rab' => 'Biaya Tunjangan Proyek',
                'norutspec' => '02',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0003',
                'spec_rab' => 'Biaya Lembur',
                'norutspec' => '02',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0004',
                'spec_rab' => 'Biaya Material & Suku Cadang',
                'norutspec' => '04',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0005',
                'spec_rab' => 'Biaya Sub Kontraktor/Konsultan (Maint)',
                'norutspec' => '04',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0006',
                'spec_rab' => 'Biaya Sewa Peralatan Proyek',
                'norutspec' => '06',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0007',
                'spec_rab' => 'Biaya Transportasi & SPD',
                'norutspec' => '05',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0008',
                'spec_rab' => 'Biaya Bunga & Asuransi Proyek',
                'norutspec' => '07',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0009',
                'spec_rab' => 'Biaya Depresiasi Asset',
                'norutspec' => '03',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0010',
                'spec_rab' => 'Biaya Komunikasi',
                'norutspec' => '05',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0011',
                'spec_rab' => 'Biaya Pelatihan',
                'norutspec' => '07',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0012',
                'spec_rab' => 'Biaya Kantor & Umum Proyek (Adm)',
                'norutspec' => '07',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0013',
                'spec_rab' => 'Biaya Tunjangan Lainnya & Relasi Proyek',
                'norutspec' => '07',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0014',
                'spec_rab' => 'Total Biaya Proyek',
                'norutspec' => '07',
                'kategori' => 'HPP',
                'status' => 'A'
            ],
            [
                'id_spec' => '0015',
                'spec_rab' => 'Target Pendapatan',
                'norutspec' => '08',
                'kategori' => 'PDP',
                'status' => 'A'
            ],

        ];

        // Delete existing data instead of truncate to avoid foreign key issues
        DB::table('spec_rab')->delete();

        // Insert data
        foreach ($spesifikasiRAB as $data) {
            DB::table('spec_rab')->insert([
                'id_spec' => $data['id_spec'],
                'spec_rab' => $data['spec_rab'],
                'norutspec' => $data['norutspec'],
                'kategori' => $data['kategori'],
                'status' => $data['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('SpesifikasiRAB seeder completed. Inserted ' . count($spesifikasiRAB) . ' records.');
    }
}
