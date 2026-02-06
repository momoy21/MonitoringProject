<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RABProyek;
use App\Models\Konsumen;
use App\Models\MasterDivisi;
use App\Models\JenisProyek;
use Carbon\Carbon;

class RABProyekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data for relations
        $konsumenIds = Konsumen::pluck('id_konsumen')->toArray();
        $divisiCodes = MasterDivisi::pluck('kode_divisi')->toArray();
        $jenisProyekCodes = JenisProyek::pluck('kode_jenis')->toArray();

        // Fallback if no data exists
        if (empty($konsumenIds)) {
            $konsumenIds = ['K00001'];
        }
        if (empty($divisiCodes)) {
            $divisiCodes = ['DT', 'ERP', 'Infra'];
        }
        if (empty($jenisProyekCodes)) {
            $jenisProyekCodes = ['IM', 'TR', 'SW', 'MN'];
        }

        $bidangJasa = ['01', '02', '03', '04', '05', '06', '07', '08'];
        $keterangan = ['P', 'T', 'R'];
        $progress = ['01', '02', '03', '04'];
        $hasilPleno = ['TT', 'TR'];
        $status = ['D', 'F'];

        // Generate sample data - 50 records across different months
        $sampleData = [];
        
        for ($i = 1; $i <= 50; $i++) {
            // Random date within the last 12 months
            $randomMonthsAgo = rand(0, 11);
            $randomDay = rand(1, 28);
            $tglInput = Carbon::now()->subMonths($randomMonthsAgo)->setDay($randomDay);

            $nilaiProyek = rand(50, 500) * 1000000; // 50 juta - 500 juta
            $marginRkap = rand(10, 30) + (rand(0, 99) / 100);
            $marginPleno = $marginRkap + rand(-5, 5) + (rand(0, 99) / 100);

            $sampleData[] = [
                'dokumen_io' => 'IO' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'cost_center' => 'CC' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT),
                'nama_project' => $this->getRandomProjectName($i),
                'id_konsumen' => $konsumenIds[array_rand($konsumenIds)],
                'id_bidjasa' => $bidangJasa[array_rand($bidangJasa)],
                'pm' => $this->getRandomPM(),
                'divisi' => $divisiCodes[array_rand($divisiCodes)],
                'jenis_proyek' => $jenisProyekCodes[array_rand($jenisProyekCodes)],
                'status' => $status[array_rand($status)],
                'nilai_proyek' => $nilaiProyek,
                'tgl_input' => $tglInput,
                'keterangan' => $keterangan[array_rand($keterangan)],
                'progress' => $progress[array_rand($progress)],
                'hasil_pleno' => $hasilPleno[array_rand($hasilPleno)],
                'catatan' => 'Sample catatan untuk proyek ' . $i,
                'margin_rkap' => $marginRkap,
                'margin_pleno' => $marginPleno,
            ];
        }

        foreach ($sampleData as $data) {
            RABProyek::create($data);
        }

        $this->command->info('RABProyek seeder completed. Created 50 sample records.');
    }

    /**
     * Get random project name
     */
    private function getRandomProjectName(int $index): string
    {
        $projectTypes = [
            'Implementasi SAP S/4HANA',
            'Sistem Monitoring IoT',
            'Dashboard Analytics',
            'Integrasi ERP',
            'Sistem Manajemen Proyek',
            'Network Infrastructure Upgrade',
            'Cloud Migration',
            'Aplikasi Mobile Warehouse',
            'Sistem Laporan Keuangan',
            'Data Center Setup',
            'Cybersecurity Implementation',
            'Automation Control System',
            'Power System Integration',
            'Hospital Management System',
            'Manufacturing Execution System',
        ];

        $clients = [
            'PT. ABC Indonesia',
            'PT. XYZ Corporation',
            'PT. Maju Jaya',
            'PT. Global Tech',
            'PT. Nusantara Sejahtera',
            'PT. Indo Prima',
            'PT. Digital Solutions',
            'PT. Smart Systems',
        ];

        return $projectTypes[array_rand($projectTypes)] . ' - ' . $clients[array_rand($clients)];
    }

    /**
     * Get random PM name
     */
    private function getRandomPM(): string
    {
        $names = [
            'Budi Santoso',
            'Ahmad Wijaya',
            'Dewi Lestari',
            'Rudi Hermawan',
            'Siti Rahayu',
            'Agus Prasetyo',
            'Rina Kusuma',
            'Hendra Gunawan',
            'Maya Putri',
            'Dimas Setiawan',
        ];

        return $names[array_rand($names)];
    }
}
