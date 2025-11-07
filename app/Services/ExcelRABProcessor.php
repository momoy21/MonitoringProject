<?php

namespace App\Services;

use App\Models\SpesifikasiRAB;
use App\Models\DetailRAB;
use App\Models\SummaryRAB;
use App\Models\SummaryDetailRAB;
use App\Imports\RABStreamImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExcelRABProcessor
{
    /**
     * Process Excel file dan simpan ke Detail RAB dan Summary Detail RAB
     */
    public function processExcelFile($filePath, $idRAB, $periodeMulai, $lamaBulan)
    {
        try {
            // Set memory limit
            ini_set('memory_limit', '1G');

            // 1. Import data Excel dari sheet "Profit&Loss"
            $importer = new RABStreamImport($lamaBulan);
            $excelData = $importer->import($filePath);

            if (empty($excelData)) {
                throw new \Exception("Tidak ada data yang berhasil dibaca dari sheet 'Profit&Loss'. Pastikan sheet dengan nama tersebut ada dan memiliki data mulai dari baris 12.");
            }

            // Free memory
            gc_collect_cycles();

            // === PROSES DETAIL RAB ===
            $this->processDetailRAB($excelData, $idRAB, $periodeMulai, $lamaBulan);

            // === PROSES SUMMARY DETAIL RAB ===
            $this->processSummaryDetailRAB($excelData, $idRAB);

            // Free memory
            unset($excelData);
            gc_collect_cycles();

            return [
                'success' => true,
                'message' => 'File Excel berhasil diproses untuk Detail RAB dan Summary Detail RAB',
                'data' => [
                    'detail_rab_processed' => true,
                    'summary_detail_rab_processed' => true
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process Detail RAB (existing logic)
     */
    private function processDetailRAB($excelData, $idRAB, $periodeMulai, $lamaBulan)
    {
        // Ambil spesifikasi RAB yang aktif, ordered by norutspec
        $configSpecRAB = SpesifikasiRAB::active()
            ->ordered()
            ->get();

        $jmlspec = $configSpecRAB->count();

        if ($jmlspec == 0) {
            throw new \Exception("Tidak ada spesifikasi RAB aktif ditemukan di database");
        }

        // Cari spec "Biaya Penjualan"
        $biayaPenjualanSpec = null;
        $biayaPenjualanIndex = -1;

        foreach ($configSpecRAB as $index => $spec) {
            if (stripos($spec->spec_rab, 'Biaya Penjualan') !== false) {
                $biayaPenjualanSpec = $spec;
                $biayaPenjualanIndex = $index;
                break;
            }
        }

        // Hapus existing detail RAB untuk ID RAB ini
        DetailRAB::where('id_rab', $idRAB)->delete();

        $detailRABData = [];
        $currentIdDetailRAB = 1;
        $batchSize = 100;
        $normalSpecsProcessed = 0;

        for ($i = 0; $i < $jmlspec; $i++) {
            $configSpec = $configSpecRAB[$i];

            $isBiayaPenjualan = ($biayaPenjualanSpec !== null && $i == $biayaPenjualanIndex);

            if ($isBiayaPenjualan) {
                $excelRowIndex = $normalSpecsProcessed + 1;
            } else {
                $excelRowIndex = $normalSpecsProcessed;
                $normalSpecsProcessed++;
            }

            $excelRow = $excelData[$excelRowIndex] ?? null;

            if (!$excelRow || !isset($excelRow['values'])) {
                $excelRow = [
                    'values' => array_fill(0, $lamaBulan + 1, 0),
                    'excel_row' => 'N/A',
                    'keterangan' => 'Data tidak tersedia'
                ];
            }

            for ($j = 0; $j <= $lamaBulan; $j++) {
                if ($j == 0) {
                    $bulan = $this->generateBulanFormatMinus1($periodeMulai);
                } else {
                    $bulan = $this->generateBulanFormatFromPeriode($periodeMulai, $j - 1);
                }

                $nilai = 0;
                if (isset($excelRow['values'][$j])) {
                    $nilai = $excelRow['values'][$j] * 1000;
                }

                $detailRABData[] = [
                    'id_rab' => $idRAB,
                    'id_detail_rab' => $currentIdDetailRAB,
                    'id_spec' => $configSpec->id_spec,
                    'bulan' => $bulan,
                    'urutbln' => $j,
                    'nilai' => $nilai,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $currentIdDetailRAB++;

                if (count($detailRABData) >= $batchSize) {
                    $this->saveDetailRABToDatabase($detailRABData);
                    $detailRABData = [];
                    gc_collect_cycles();
                }
            }
        }

        if (!empty($detailRABData)) {
            $this->saveDetailRABToDatabase($detailRABData);
        }
    }

    /**
     * Process Summary Detail RAB (new logic)
     * Menggunakan struktur summary_rab yang sudah ada:
     * - idsummary: varchar(4)
     * - ketsummaryrab: varchar(100)
     * - norutsummary: varchar(2)
     * - status: char(1) - 'A' = Aktif, 'N' = Non Aktif
     */
    private function processSummaryDetailRAB($excelData, $idRAB)
    {
        // Ambil summary RAB yang aktif (status = 'A'), ordered by norutsummary
        $configSummaryRAB = SummaryRAB::active()
            ->ordered()
            ->get();

        $jmlsummary = $configSummaryRAB->count();

        if ($jmlsummary == 0) {
            return;
        }

        // Hapus existing summary detail RAB untuk ID RAB ini
        SummaryDetailRAB::where('id_rab', $idRAB)->delete();

        $summaryDetailRABData = [];
        $currentIdSummaryRAB = 1;
        $startRow = 30; // Baris 30 di Excel (index 30-12 = 18 dalam array karena mulai dari baris 12)
        $arrayStartIndex = $startRow - 12; // Convert Excel row to array index

        // FOR i = 1 TO jmlsummary LOOP
        for ($i = 0; $i < $jmlsummary; $i++) {
            $configSummary = $configSummaryRAB[$i];
            $excelRowIndex = $arrayStartIndex + $i;

            // Ambil data Excel untuk baris ini
            $excelRow = $excelData[$excelRowIndex] ?? null;

            // Jika baris Excel tidak ada atau kolom F benar-benar kosong (null), skip entry
            if (!$excelRow || !array_key_exists('nilai_kolom_f', $excelRow) || $excelRow['nilai_kolom_f'] === null) {
                // Jangan menambahkan entry dan jangan menaikkan id summary
                continue;
            }

            // Ambil nilai dari kolom F (sudah di-parse di RABStreamImport)
            $nilai = $excelRow['nilai_kolom_f'] * 1000;

            $summaryDetailRABData[] = [
                'id_rab' => $idRAB,
                'id_summary_rab' => $currentIdSummaryRAB,
                'idsummary' => $configSummary->idsummary,
                'nilai' => $nilai,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $currentIdSummaryRAB++;
        }

        // Simpan ke database
        if (!empty($summaryDetailRABData)) {
            $this->saveSummaryDetailRABToDatabase($summaryDetailRABData);
        }
    }

    /**
     * Generate format bulan untuk j=0 (periode - 1 bulan)
     */
    private function generateBulanFormatMinus1($periodeMulai)
    {
        $tanggalMulai = Carbon::createFromFormat('d/m/Y', $periodeMulai);
        $targetMonth = $tanggalMulai->copy()->subMonth();
        return $targetMonth->format('M Y');
    }

    /**
     * Generate format bulan dari periode + additional months
     */
    private function generateBulanFormatFromPeriode($periodeMulai, $additionalMonths)
    {
        $tanggalMulai = Carbon::createFromFormat('d/m/Y', $periodeMulai);
        $targetMonth = $tanggalMulai->copy()->addMonths($additionalMonths);
        return $targetMonth->format('M Y');
    }

    /**
     * Simpan data Detail RAB ke database
     */
    private function saveDetailRABToDatabase($data)
    {
        try {
            if (empty($data)) {
                return;
            }

            $chunkSize = 100;
            foreach (array_chunk($data, $chunkSize) as $chunk) {
                $values = [];
                $bindings = [];

                foreach ($chunk as $record) {
                    $values[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                    $bindings = array_merge($bindings, [
                        $record['id_rab'],
                        $record['id_detail_rab'],
                        $record['id_spec'],
                        $record['bulan'],
                        $record['urutbln'],
                        $record['nilai'],
                        $record['created_at']->toDateTimeString(),
                        $record['updated_at']->toDateTimeString()
                    ]);
                }

                $sql = "INSERT INTO detail_rab (id_rab, id_detail_rab, id_spec, bulan, urutbln, nilai, created_at, updated_at) VALUES " . implode(', ', $values);
                DB::insert($sql, $bindings);

                unset($chunk, $values, $bindings);
                gc_collect_cycles();
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Simpan data Summary Detail RAB ke database
     */
    private function saveSummaryDetailRABToDatabase($data)
    {
        try {
            if (empty($data)) {
                return;
            }

            $chunkSize = 100;
            foreach (array_chunk($data, $chunkSize) as $chunk) {
                $values = [];
                $bindings = [];

                foreach ($chunk as $record) {
                    $values[] = '(?, ?, ?, ?, ?, ?)';
                    $bindings = array_merge($bindings, [
                        $record['id_rab'],
                        $record['id_summary_rab'],
                        $record['idsummary'],
                        $record['nilai'],
                        $record['created_at']->toDateTimeString(),
                        $record['updated_at']->toDateTimeString()
                    ]);
                }

                $sql = "INSERT INTO summary_rab_detail (id_rab, id_summary_rab, idsummary, nilai, created_at, updated_at) VALUES " . implode(', ', $values);
                DB::insert($sql, $bindings);

                unset($chunk, $values, $bindings);
                gc_collect_cycles();
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
