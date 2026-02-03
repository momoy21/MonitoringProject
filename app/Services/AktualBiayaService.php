<?php

namespace App\Services;

use App\Models\Plsap;
use App\Models\AktualBiaya;
use App\Models\SpecRabDetail;
use App\Models\SpesifikasiRAB;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AktualBiayaService
{
    protected string $logFolder;

    public function __construct()
    {
        $this->logFolder = storage_path('app/sap/LOG');
        $this->ensureLogFolderExists();
    }

    /**
     * Pastikan folder log ada
     */
    protected function ensureLogFolderExists(): void
    {
        if (!is_dir($this->logFolder)) {
            mkdir($this->logFolder, 0755, true);
        }
    }

    /**
     * Log aktivitas mapping
     */
    protected function logMapping(string $status, string $message): void
    {
        $logFile = $this->logFolder . '/aktual_biaya_mapping_' . date('Ymd') . '.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$status}] {$message}" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Proses mapping dari PLSAP ke Aktual Biaya
     * 
     * @param Collection|null $plsapRecords Records SAP yang akan diproses (null = semua yang belum diproses)
     * @param bool $reprocess Jika true, proses ulang semua termasuk yang sudah ada
     * @return array Hasil proses mapping
     */
    public function processMapping(?Collection $plsapRecords = null, bool $reprocess = false): array
    {
        $this->logMapping('STARTED', 'Memulai proses mapping SAP ke Aktual Biaya');

        $result = [
            'success' => true,
            'total_processed' => 0,
            'total_mapped' => 0,
            'total_skipped' => 0,
            'total_unmapped' => 0,
            'errors' => [],
            'unmapped_cost_elements' => [],
        ];

        try {
            // Jika tidak ada records yang diberikan, ambil semua dari plsap
            if ($plsapRecords === null) {
                if ($reprocess) {
                    $plsapRecords = Plsap::all();
                } else {
                    // Ambil hanya yang belum ada di aktual_biaya
                    $existingPlsapIds = AktualBiaya::whereNotNull('plsap_id')
                                                   ->pluck('plsap_id')
                                                   ->toArray();
                    $plsapRecords = Plsap::whereNotIn('id', $existingPlsapIds)->get();
                }
            }

            $result['total_processed'] = $plsapRecords->count();
            $this->logMapping('INFO', "Total records SAP yang akan diproses: {$result['total_processed']}");

            if ($result['total_processed'] === 0) {
                $this->logMapping('INFO', 'Tidak ada records baru untuk diproses');
                return $result;
            }

            // Cache mapping cost_element -> spec_rab untuk performa
            $costElementMapping = $this->buildCostElementMapping();

            DB::beginTransaction();

            foreach ($plsapRecords as $plsap) {
                $mappingResult = $this->mapSingleRecord($plsap, $costElementMapping);

                if ($mappingResult['status'] === 'mapped') {
                    $result['total_mapped']++;
                } elseif ($mappingResult['status'] === 'skipped') {
                    $result['total_skipped']++;
                } elseif ($mappingResult['status'] === 'unmapped') {
                    $result['total_unmapped']++;
                    
                    // Track cost elements yang tidak ada mapping
                    $costElement = $plsap->cost_element;
                    if (!in_array($costElement, $result['unmapped_cost_elements'])) {
                        $result['unmapped_cost_elements'][] = $costElement;
                    }
                } elseif ($mappingResult['status'] === 'error') {
                    $result['errors'][] = $mappingResult['message'];
                }
            }

            DB::commit();

            $this->logMapping('SUCCESS', sprintf(
                'Proses selesai. Mapped: %d, Skipped: %d, Unmapped: %d, Errors: %d',
                $result['total_mapped'],
                $result['total_skipped'],
                $result['total_unmapped'],
                count($result['errors'])
            ));

            if (!empty($result['unmapped_cost_elements'])) {
                $this->logMapping('WARNING', 'Cost Elements tanpa mapping: ' . implode(', ', $result['unmapped_cost_elements']));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->logMapping('ERROR', 'Proses gagal: ' . $e->getMessage());
            Log::error('AktualBiaya Mapping Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return $result;
    }

    /**
     * Build cache mapping cost_element ke spec_rab
     */
    protected function buildCostElementMapping(): array
    {
        $mapping = [];
        
        $details = SpecRabDetail::with('spesifikasiRab')->get();
        
        foreach ($details as $detail) {
            $mapping[$detail->cost_element] = [
                'id_spec' => $detail->id_spec,
                'kategori' => $detail->spesifikasiRab?->kategori ?? 'HPP', // Default HPP jika tidak ada
            ];
        }

        $this->logMapping('INFO', 'Loaded ' . count($mapping) . ' cost element mappings');

        return $mapping;
    }

    /**
     * Map single PLSAP record ke Aktual Biaya
     */
    protected function mapSingleRecord(Plsap $plsap, array $costElementMapping): array
    {
        // Validasi cc_projek tidak kosong
        if (empty($plsap->cc_projek)) {
            return [
                'status' => 'skipped',
                'message' => "Record #{$plsap->id}: CCProjek kosong",
            ];
        }

        // Cek apakah cost_element ada di mapping
        $costElement = $plsap->cost_element;
        
        if (!isset($costElementMapping[$costElement])) {
            return [
                'status' => 'unmapped',
                'message' => "Record #{$plsap->id}: Cost Element '{$costElement}' tidak ditemukan di mapping",
            ];
        }

        $mappingInfo = $costElementMapping[$costElement];

        try {
            // Parse tanggal posting
            $tanggalPosting = $this->parsePostingDate($plsap->posting_date);
            
            if (!$tanggalPosting) {
                return [
                    'status' => 'error',
                    'message' => "Record #{$plsap->id}: Format tanggal posting tidak valid",
                ];
            }

            // Bulan = awal bulan dari tanggal posting
            $bulan = $tanggalPosting->copy()->startOfMonth();

            // Generate ID Aktual
            $idAktual = AktualBiaya::generateIdAktual();

            // Cek apakah sudah ada record yang sama (berdasarkan plsap_id)
            $existing = AktualBiaya::where('plsap_id', $plsap->id)->first();
            
            if ($existing) {
                return [
                    'status' => 'skipped',
                    'message' => "Record #{$plsap->id}: Sudah ada di aktual_biaya",
                ];
            }

            // Buat record Aktual Biaya
            AktualBiaya::create([
                'cc_projek' => $plsap->cc_projek,
                'id_aktual' => $idAktual,
                'id_spec' => $mappingInfo['id_spec'],
                'tanggal_posting' => $tanggalPosting->format('Y-m-d'),
                'bulan' => $bulan->format('Y-m-d'),
                'nilai' => $plsap->amount_local ?? 0,
                'kategori' => $mappingInfo['kategori'],
                'plsap_id' => $plsap->id,
            ]);

            return [
                'status' => 'mapped',
                'message' => "Record #{$plsap->id}: Berhasil dimapping ke {$idAktual}",
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => "Record #{$plsap->id}: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse tanggal posting dari berbagai format
     */
    protected function parsePostingDate($date): ?Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return Carbon::instance($date);
        }

        if (is_string($date)) {
            // Format: YYYYMMDD
            if (preg_match('/^\d{8}$/', $date)) {
                return Carbon::createFromFormat('Ymd', $date);
            }

            // Format: YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return Carbon::parse($date);
            }

            // Format: DD/MM/YYYY
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                return Carbon::createFromFormat('d/m/Y', $date);
            }

            // Try general parse
            try {
                return Carbon::parse($date);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Proses mapping untuk source file tertentu
     */
    public function processBySourceFile(string $sourceFile): array
    {
        $plsapRecords = Plsap::where('source_file', $sourceFile)->get();
        return $this->processMapping($plsapRecords);
    }

    /**
     * Get summary aktual biaya per proyek
     */
    public function getSummaryByProject(string $ccProjek): array
    {
        $data = AktualBiaya::byProject($ccProjek)
                          ->selectRaw('bulan, kategori, SUM(nilai) as total_nilai')
                          ->groupBy('bulan', 'kategori')
                          ->orderBy('bulan')
                          ->get();

        $summary = [
            'cc_projek' => $ccProjek,
            'total_pendapatan' => 0,
            'total_hpp' => 0,
            'total_laba_kotor' => 0,
            'details' => [],
        ];

        foreach ($data as $item) {
            $bulanKey = Carbon::parse($item->bulan)->format('Y-m');
            
            if (!isset($summary['details'][$bulanKey])) {
                $summary['details'][$bulanKey] = [
                    'bulan' => $bulanKey,
                    'bulan_formatted' => Carbon::parse($item->bulan)->format('M Y'),
                    'pendapatan' => 0,
                    'hpp' => 0,
                    'laba_kotor' => 0,
                ];
            }

            if ($item->kategori === 'PDP') {
                $summary['details'][$bulanKey]['pendapatan'] = (float) $item->total_nilai;
                $summary['total_pendapatan'] += (float) $item->total_nilai;
            } else {
                $summary['details'][$bulanKey]['hpp'] = (float) $item->total_nilai;
                $summary['total_hpp'] += (float) $item->total_nilai;
            }
        }

        // Hitung laba kotor per bulan
        foreach ($summary['details'] as $key => &$detail) {
            $detail['laba_kotor'] = $detail['pendapatan'] - $detail['hpp'];
        }

        // Hitung total laba kotor
        $summary['total_laba_kotor'] = $summary['total_pendapatan'] - $summary['total_hpp'];

        // Convert ke array indexed
        $summary['details'] = array_values($summary['details']);

        return $summary;
    }

    /**
     * Get unmapped cost elements
     */
    public function getUnmappedCostElements(): array
    {
        // Ambil semua cost elements dari plsap
        $plsapCostElements = Plsap::distinct('cost_element')
                                  ->pluck('cost_element')
                                  ->toArray();

        // Ambil semua cost elements yang sudah ada mapping
        $mappedCostElements = SpecRabDetail::pluck('cost_element')->toArray();

        // Cari yang belum ada mapping
        $unmapped = array_diff($plsapCostElements, $mappedCostElements);

        // Get description untuk setiap unmapped cost element
        $result = [];
        foreach ($unmapped as $costElement) {
            $plsap = Plsap::where('cost_element', $costElement)->first();
            $result[] = [
                'cost_element' => $costElement,
                'description_ce' => $plsap?->description_ce ?? '',
                'count' => Plsap::where('cost_element', $costElement)->count(),
            ];
        }

        return $result;
    }

    /**
     * Get mapping logs
     */
    public function getMappingLogs(string $date = null): string
    {
        $date = $date ?? date('Ymd');
        $logFile = $this->logFolder . '/aktual_biaya_mapping_' . $date . '.txt';

        if (file_exists($logFile)) {
            return file_get_contents($logFile);
        }

        return "Log tidak ditemukan untuk tanggal: {$date}";
    }
}
