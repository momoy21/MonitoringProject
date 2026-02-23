<?php

namespace App\Http\Controllers;

use App\Models\KuotaLembur;
use App\Models\DataProyek;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KuotaLemburController extends Controller
{
    public function index(Request $request)
    {
        return view('rencanelembur.index');
    }

    /**
     * Pre-load initial page data (cost centers + karyawan) in one call
     */
    public function getInitialData(Request $request)
    {
        $costCenters = Cache::remember('kuota_lembur_cost_centers', 300, function () {
            return DataProyek::select('cost_center', 'namaproject', 'dokumen_io')
                ->whereNotNull('cost_center')
                ->where('cost_center', '!=', '')
                ->orderBy('cost_center')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    $costCenter = $item->cost_center ?? '-';
                    $namaProject = $item->namaproject ?? '-';
                    $dokumenIO = $item->dokumen_io ?? '-';
                    return [
                        'id' => $costCenter,
                        'text' => "{$costCenter} - {$namaProject} - {$dokumenIO}",
                        'cost_center' => $costCenter,
                        'namaproject' => $namaProject,
                        'dokumen_io' => $dokumenIO,
                    ];
                });
        });

        $karyawan = Cache::remember('kuota_lembur_karyawan', 300, function () {
            return Karyawan::active()
                ->orderBy('nama')
                ->get(['nik', 'nama'])
                ->map(function ($item) {
                    return [
                        'id' => $item->nik,
                        'text' => "{$item->nik} - {$item->nama}",
                        'nik' => $item->nik,
                        'nama' => $item->nama,
                    ];
                });
        });

        return response()->json([
            'cost_centers' => $costCenters,
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Get Cost Center dropdown data from data_proyek
     */
    public function getCostCenterDropdown(Request $request)
    {
        $search = $request->get('search', '');

        $query = DataProyek::select('cost_center', 'namaproject', 'dokumen_io')
            ->whereNotNull('cost_center')
            ->where('cost_center', '!=', '');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cost_center', 'LIKE', "%{$search}%")
                    ->orWhere('namaproject', 'LIKE', "%{$search}%")
                    ->orWhere('dokumen_io', 'LIKE', "%{$search}%");
            });
        }

        $projects = $query->orderBy('cost_center')
            ->distinct()
            ->limit(50)
            ->get();

        $results = $projects->map(function ($item) {
            $costCenter = $item->cost_center ?? '-';
            $namaProject = $item->namaproject ?? '-';
            $dokumenIO = $item->dokumen_io ?? '-';

            return [
                'id' => $costCenter,
                'text' => "{$costCenter} - {$namaProject} - {$dokumenIO}",
                'cost_center' => $costCenter,
                'namaproject' => $namaProject,
                'dokumen_io' => $dokumenIO,
            ];
        });

        return response()->json($results);
    }

    /**
     * Get kuota lembur data filtered by cost_center
     */
    public function getData(Request $request)
    {
        $costCenter = $request->get('cost_center');
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);

        if (!$costCenter) {
            return response()->json([
                'success' => false,
                'message' => 'Cost Center harus dipilih'
            ], 400);
        }

        try {
            $query = KuotaLembur::where('kuota_lembur.cost_center', $costCenter)
                ->join('karyawan', 'kuota_lembur.nik', '=', 'karyawan.nik')
                ->select(
                    'kuota_lembur.cost_center',
                    'kuota_lembur.nik',
                    'kuota_lembur.bulan',
                    'kuota_lembur.periode_awal',
                    'kuota_lembur.periode_akhir',
                    'kuota_lembur.jml_wd',
                    'kuota_lembur.jml_we',
                    'kuota_lembur.jml_hn',
                    'kuota_lembur.status',
                    'karyawan.nama as nama_karyawan'
                );

            if ($search) {
                $words = explode(' ', $search);
                foreach ($words as $word) {
                    $word = trim($word);
                    if ($word === '') continue;
                    $upper = strtoupper($word);
                    $query->where(function ($q) use ($upper) {
                        $q->whereRaw("UPPER(kuota_lembur.nik) LIKE ?", ['%' . $upper . '%'])
                            ->orWhereRaw("UPPER(karyawan.nama) LIKE ?", ['%' . $upper . '%']);
                    });
                }
            }

            $data = $query->orderBy('kuota_lembur.created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem() ?? 0,
                    'to' => $data->lastItem() ?? 0,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kuota lembur data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next bulan sequence for a given cost_center + nik
     */
    public function getNextBulan(Request $request)
    {
        $costCenter = $request->get('cost_center');
        $nik = $request->get('nik');

        if (!$costCenter || !$nik) {
            return response()->json(['bulan' => 1]);
        }

        $maxBulan = KuotaLembur::where('cost_center', $costCenter)
            ->where('nik', $nik)
            ->max('bulan');

        return response()->json([
            'bulan' => ($maxBulan ?? 0) + 1
        ]);
    }

    /**
     * Store new kuota lembur (with duplicate detection & replace support)
     */
    public function store(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'cost_center' => 'required|string|max:20',
            'nik' => 'required|string|max:9',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'jml_wd' => 'nullable|numeric|min:0',
            'jml_we' => 'nullable|numeric|min:0',
            'jml_hn' => 'nullable|numeric|min:0',
            'replace' => 'nullable|boolean',
        ], [
            'cost_center.required' => 'Cost Center wajib diisi',
            'nik.required' => 'NIK wajib diisi',
            'periode_awal.required' => 'Periode Awal wajib diisi',
            'periode_akhir.required' => 'Periode Akhir wajib diisi',
        ]);

        $replace = $request->boolean('replace', false);

        // Custom validations
        if (Carbon::parse($validated['periode_awal'])->gt(Carbon::parse($validated['periode_akhir']))) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak valid'
            ], 422);
        }

        // Validate NIK exists in karyawan table
        $karyawan = Karyawan::where('nik', $validated['nik'])->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => "NIK '{$validated['nik']}' tidak ditemukan di data karyawan"
            ], 422);
        }

        // Validate cost_center exists in data_proyek
        $project = DataProyek::where('cost_center', $validated['cost_center'])->first();
        if (!$project || empty($project->dokumen_io)) {
            return response()->json([
                'success' => false,
                'message' => "Cost Center '{$validated['cost_center']}' tidak ditemukan di data proyek"
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get dokumen_io from data_proyek
            $dokIo = $project->dokumen_io;

            // Check for existing record with same cost_center + nik + periode_awal
            $existing = KuotaLembur::where('cost_center', $validated['cost_center'])
                ->where('nik', $validated['nik'])
                ->whereDate('periode_awal', $validated['periode_awal'])
                ->first();

            if ($existing) {
                if (!$replace) {
                    // Duplicate found, ask user to confirm replace
                    DB::rollBack();
                    $nama = Karyawan::where('nik', $validated['nik'])->value('nama') ?? '-';
                    return response()->json([
                        'success' => false,
                        'duplicate' => true,
                        'message' => 'Data duplikat ditemukan',
                        'existing' => [
                            'bulan' => $existing->bulan,
                            'nik' => $existing->nik,
                            'nama' => $nama,
                            'periode_awal' => $existing->periode_awal ? $existing->periode_awal->format('Y-m-d') : null,
                            'periode_akhir' => $existing->periode_akhir ? $existing->periode_akhir->format('Y-m-d') : null,
                            'jml_wd' => $existing->jml_wd,
                            'jml_we' => $existing->jml_we,
                            'jml_hn' => $existing->jml_hn,
                        ],
                    ], 409);
                }

                // Replace: update existing record
                $existing->update([
                    'periode_awal' => $validated['periode_awal'],
                    'periode_akhir' => $validated['periode_akhir'],
                    'jml_wd' => $validated['jml_wd'] ?? 0,
                    'jml_we' => $validated['jml_we'] ?? 0,
                    'jml_hn' => $validated['jml_hn'] ?? 0,
                    'status' => null,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data kuota lembur berhasil diganti (replace)',
                    'data' => $existing,
                ]);
            }

            // No duplicate — create new record with auto-incremented bulan
            $maxBulan = KuotaLembur::where('cost_center', $validated['cost_center'])
                ->where('nik', $validated['nik'])
                ->max('bulan');
            $bulan = ($maxBulan ?? 0) + 1;

            $kuota = KuotaLembur::create([
                'cost_center' => $validated['cost_center'],
                'dok_io' => $dokIo,
                'nik' => $validated['nik'],
                'bulan' => $bulan,
                'periode_awal' => $validated['periode_awal'],
                'periode_akhir' => $validated['periode_akhir'],
                'jml_wd' => $validated['jml_wd'] ?? 0,
                'jml_we' => $validated['jml_we'] ?? 0,
                'jml_hn' => $validated['jml_hn'] ?? 0,
                'status' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kuota lembur berhasil disimpan',
                'data' => $kuota
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing kuota lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update kuota lembur (only periode and jml fields)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'jml_wd' => 'nullable|numeric|min:0',
            'jml_we' => 'nullable|numeric|min:0',
            'jml_hn' => 'nullable|numeric|min:0',
            'cost_center' => 'required|string',
            'nik' => 'required|string',
            'bulan' => 'required|integer',
        ]);

        // Custom validations
        if (Carbon::parse($validated['periode_awal'])->gt(Carbon::parse($validated['periode_akhir']))) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak valid'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $kuota = KuotaLembur::where('cost_center', $validated['cost_center'])
                ->where('nik', $validated['nik'])
                ->where('bulan', $validated['bulan'])
                ->firstOrFail();

            $kuota->update([
                'periode_awal' => $validated['periode_awal'],
                'periode_akhir' => $validated['periode_akhir'],
                'jml_wd' => $validated['jml_wd'] ?? 0,
                'jml_we' => $validated['jml_we'] ?? 0,
                'jml_hn' => $validated['jml_hn'] ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kuota lembur berhasil diperbarui',
                'data' => $kuota
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating kuota lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete kuota lembur
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'cost_center' => 'required|string',
            'nik' => 'required|string',
            'bulan' => 'required|integer',
        ]);

        try {
            $kuota = KuotaLembur::where('cost_center', $validated['cost_center'])
                ->where('nik', $validated['nik'])
                ->where('bulan', $validated['bulan'])
                ->firstOrFail();

            $kuota->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data kuota lembur berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting kuota lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Excel file and import data (2-phase: check duplicates first, then import)
     *
     * Phase 1 (default / check_only=true): Parse file, detect duplicates, return preview.
     *         If NO duplicates → import immediately and return success.
     * Phase 2 (confirm_replace=true): Import all rows, replacing duplicates.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File wajib diunggah',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        $confirmReplace = $request->boolean('confirm_replace', false);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, false, true);

            // --- First pass: parse & validate all rows ---
            $parsedRows = [];
            $errors = [];
            $duplicates = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) continue; 

                $costCenter = trim($row['A'] ?? '');
                $nik = trim($row['B'] ?? '');
                $bulan = intval($row['C'] ?? 0);
                $periodeAwalRaw = trim($row['D'] ?? '');
                $periodeAkhirRaw = trim($row['E'] ?? '');
                $jmlWD = floatval($row['F'] ?? 0);
                $jmlWE = floatval($row['G'] ?? 0);
                $jmlHN = floatval($row['H'] ?? 0);

                if (empty($costCenter) && empty($nik)) continue;

                if (empty($costCenter)) {
                    $errors[] = "Baris {$index}: Cost Center wajib diisi";
                    continue;
                }
                if (empty($nik)) {
                    $errors[] = "Baris {$index}: NIK wajib diisi";
                    continue;
                }
                if (empty($periodeAwalRaw)) {
                    $errors[] = "Baris {$index}: Periode Awal wajib diisi";
                    continue;
                }
                if (empty($periodeAkhirRaw)) {
                    $errors[] = "Baris {$index}: Periode Akhir wajib diisi";
                    continue;
                }

                // Parse dates
                try {
                    $periodeAwal = $this->parseExcelDate($periodeAwalRaw);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$index}: Periode Awal tidak valid ('{$periodeAwalRaw}'), gunakan format dd/mm/yyyy";
                    continue;
                }
                try {
                    $periodeAkhir = $this->parseExcelDate($periodeAkhirRaw);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$index}: Periode Akhir tidak valid ('{$periodeAkhirRaw}'), gunakan format dd/mm/yyyy";
                    continue;
                }

                if ($periodeAwal->gt($periodeAkhir)) {
                    $errors[] = "Baris {$index}: Periode Awal ({$periodeAwal->format('d/m/Y')}) lebih besar dari Periode Akhir ({$periodeAkhir->format('d/m/Y')})";
                    continue;
                }

                $project = DataProyek::where('cost_center', $costCenter)->first();
                if (!$project || empty($project->dokumen_io)) {
                    $errors[] = "Baris {$index}: Cost Center '{$costCenter}' tidak ditemukan di data proyek";
                    continue;
                }
                $dokIo = $project->dokumen_io;

                // Validate NIK exists in karyawan table
                $karyawanExists = Karyawan::where('nik', $nik)->exists();
                if (!$karyawanExists) {
                    $errors[] = "Baris {$index}: NIK '{$nik}' tidak ditemukan di data karyawan";
                    continue;
                }

                // Check for duplicate: same cost_center + nik + periode_awal
                $existing = KuotaLembur::where('cost_center', $costCenter)
                    ->where('nik', $nik)
                    ->whereDate('periode_awal', $periodeAwal->format('Y-m-d'))
                    ->first();

                if ($existing) {
                    $nama = Karyawan::where('nik', $nik)->value('nama') ?? '-';
                    $duplicates[] = [
                        'row' => $index,
                        'cost_center' => $costCenter,
                        'nik' => $nik,
                        'nama' => $nama,
                        'periode_awal' => $periodeAwal->format('d/m/Y'),
                        'existing_bulan' => $existing->bulan,
                        'existing_periode_akhir' => $existing->periode_akhir ? $existing->periode_akhir->format('d/m/Y') : '-',
                        'existing_jml_wd' => $existing->jml_wd,
                        'existing_jml_we' => $existing->jml_we,
                        'existing_jml_hn' => $existing->jml_hn,
                    ];
                }

                $parsedRows[] = [
                    'index' => $index,
                    'cost_center' => $costCenter,
                    'nik' => $nik,
                    'bulan' => $bulan,
                    'periode_awal' => $periodeAwal,
                    'periode_akhir' => $periodeAkhir,
                    'jml_wd' => $jmlWD,
                    'jml_we' => $jmlWE,
                    'jml_hn' => $jmlHN,
                    'dok_io' => $dokIo,
                    'has_existing' => $existing ? true : false,
                    'existing_bulan' => $existing ? $existing->bulan : null,
                ];
            }

            // If duplicates found and user has NOT confirmed replace → return preview
            if (count($duplicates) > 0 && !$confirmReplace) {
                return response()->json([
                    'success' => false,
                    'has_duplicates' => true,
                    'duplicates' => $duplicates,
                    'total_rows' => count($parsedRows),
                    'new_rows' => count($parsedRows) - count($duplicates),
                    'duplicate_count' => count($duplicates),
                    'errors' => $errors,
                    'message' => 'Ditemukan ' . count($duplicates) . ' data duplikat. Konfirmasi untuk mengganti.',
                ], 409);
            }

            // --- Second pass: import data ---
            DB::beginTransaction();
            $imported = 0;

            foreach ($parsedRows as $parsed) {
                $now = Carbon::now();

                if ($parsed['has_existing']) {
                    // Duplicate row → update existing record (use existing bulan)
                    KuotaLembur::where('cost_center', $parsed['cost_center'])
                        ->where('nik', $parsed['nik'])
                        ->where('bulan', $parsed['existing_bulan'])
                        ->update([
                            'dok_io' => $parsed['dok_io'],
                            'periode_awal' => $parsed['periode_awal']->format('Y-m-d'),
                            'periode_akhir' => $parsed['periode_akhir']->format('Y-m-d'),
                            'jml_wd' => $parsed['jml_wd'],
                            'jml_we' => $parsed['jml_we'],
                            'jml_hn' => $parsed['jml_hn'],
                            'status' => null,
                            'updated_at' => $now,
                        ]);
                } else {
                    // New row — auto-calculate bulan if not provided
                    $bulan = $parsed['bulan'];
                    if ($bulan <= 0) {
                        $maxBulan = KuotaLembur::where('cost_center', $parsed['cost_center'])
                            ->where('nik', $parsed['nik'])
                            ->max('bulan');
                        $bulan = ($maxBulan ?? 0) + 1;
                    }

                    KuotaLembur::updateOrCreate(
                        [
                            'cost_center' => $parsed['cost_center'],
                            'nik' => $parsed['nik'],
                            'bulan' => $bulan,
                        ],
                        [
                            'dok_io' => $parsed['dok_io'],
                            'periode_awal' => $parsed['periode_awal']->format('Y-m-d'),
                            'periode_akhir' => $parsed['periode_akhir']->format('Y-m-d'),
                            'jml_wd' => $parsed['jml_wd'],
                            'jml_we' => $parsed['jml_we'],
                            'jml_hn' => $parsed['jml_hn'],
                            'status' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }

                $imported++;
            }

            DB::commit();

            // Determine response
            if ($imported === 0 && count($errors) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang berhasil diimpor. ' . count($errors) . ' baris gagal.',
                    'imported' => 0,
                    'errors' => $errors,
                ], 422);
            }

            $replacedCount = count($duplicates);
            $message = "{$imported} data berhasil diimpor.";
            if ($replacedCount > 0) {
                $message .= " ({$replacedCount} data diganti/replace).";
            }
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " baris gagal.";
            }

            return response()->json([
                'success' => true,
                'has_errors' => count($errors) > 0,
                'has_duplicates' => false,
                'message' => $message,
                'imported' => $imported,
                'replaced' => $replacedCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading kuota lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data. Pastikan format file sesuai template.'
            ], 500);
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Kuota Lembur');

        // Headers
        $headers = [
            'A1' => 'Cost Center',
            'B1' => 'NIK',
            'C1' => 'Bulan Ke',
            'D1' => 'Periode Awal',
            'E1' => 'Periode Akhir',
            'F1' => 'Jumlah WeekDay',
            'G1' => 'Jumlah WeekEnd',
            'H1' => 'Jumlah Hari Nasional /Kalender',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Sample data
        $sampleData = [
            ['KH42601', '3000100', 1, '01/01/2026', '31/01/2026', 5, 10, 5],
            ['KH42601', '3000100', 2, '01/02/2026', '28/02/2026', 5, 10, ''],
            ['KH42601', '3000100', 3, '01/03/2026', '31/03/2026', 5, 10, ''],
            ['KH42601', '3000100', 4, '01/04/2026', '30/04/2026', 5, 10, ''],
            ['KH42601', '3000100', 5, '01/05/2026', '31/05/2026', 5, 10, ''],
            ['KH42601', '3090546', 1, '01/01/2026', '31/01/2026', 5, 10, ''],
            ['KH42601', '3090546', 2, '01/02/2026', '28/02/2026', 5, 10, ''],
            ['KH42601', '3090546', 3, '01/03/2026', '31/03/2026', 5, 10, ''],
        ];

        $row = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue("A{$row}", $data[0]);
            $sheet->setCellValue("B{$row}", $data[1]);
            $sheet->setCellValue("C{$row}", $data[2]);
            $sheet->setCellValue("D{$row}", $data[3]);
            $sheet->setCellValue("E{$row}", $data[4]);
            $sheet->setCellValue("F{$row}", $data[5]);
            $sheet->setCellValue("G{$row}", $data[6]);
            $sheet->setCellValue("H{$row}", $data[7]);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $lastRow = $row - 1;
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray($styleArray);

        // Output
        $filename = 'template_kuota_lembur.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $temp = tempnam(sys_get_temp_dir(), 'kuotalembur');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Get active karyawan for NIK dropdown (kept for backward compat, prefer getInitialData)
     */
    public function getKaryawanDropdown(Request $request)
    {
        $search = $request->get('search', '');

        $query = Karyawan::active();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $karyawan = $query->orderBy('nama')
            ->limit(50)
            ->get(['nik', 'nama']);

        $results = $karyawan->map(function ($item) {
            return [
                'id' => $item->nik,
                'text' => "{$item->nik} - {$item->nama}",
                'nik' => $item->nik,
                'nama' => $item->nama,
            ];
        });

        return response()->json($results);
    }

    /**
     * Flush cached data (call after import or when proyek/karyawan data changes)
     */
    public function flushCache()
    {
        Cache::forget('kuota_lembur_cost_centers');
        Cache::forget('kuota_lembur_karyawan');
        return response()->json(['success' => true]);
    }

    /**
     * Parse an Excel date value (serial number or string) into a Carbon instance.
     * Throws \Exception if the value cannot be parsed.
     */
    private function parseExcelDate($value): Carbon
    {
        if ($value === null || $value === '') {
            throw new \Exception('Date value is empty');
        }

        // Excel serial number (most reliable when formatData=false)
        if (is_numeric($value) && (float)$value > 100) {
            return Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)
            );
        }

        $value = trim((string)$value);

        // Try common date formats
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: let Carbon try to parse it
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Exception $e) {
            throw new \Exception("Cannot parse date: {$value}");
        }
    }
}
