<?php

namespace App\Http\Controllers;

use App\Models\KuotaLembur;
use App\Models\DataProyek;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KuotaLemburController extends Controller
{
    /**
     * Display main page
     */
    public function index(Request $request)
    {
        return view('rencanelembur.index');
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
                    'kuota_lembur.*',
                    'karyawan.nama as nama_karyawan'
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kuota_lembur.nik', 'LIKE', "%{$search}%")
                      ->orWhere('karyawan.nama', 'LIKE', "%{$search}%");
                });
            }

            $data = $query->orderBy('kuota_lembur.nik')
                ->orderBy('kuota_lembur.bulan')
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
     * Store new kuota lembur
     */
    public function store(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'cost_center' => 'required|string|max:20',
            'nik' => 'required|string|max:9',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'jml_wd' => 'nullable|integer|min:0',
            'jml_we' => 'nullable|integer|min:0',
            'jml_hn' => 'nullable|integer|min:0',
        ], [
            'cost_center.required' => 'Cost Center wajib diisi',
            'nik.required' => 'NIK wajib diisi',
            'periode_awal.required' => 'Periode Awal wajib diisi',
            'periode_akhir.required' => 'Periode Akhir wajib diisi',
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

            // Get dokumen_io from data_proyek
            $project = DataProyek::where('cost_center', $validated['cost_center'])->first();
            $dokIo = $project->dokumen_io ?? null;

            // Calculate bulan sequence
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'jml_wd' => 'nullable|integer|min:0',
            'jml_we' => 'nullable|integer|min:0',
            'jml_hn' => 'nullable|integer|min:0',
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

            $kuota = KuotaLembur::findOrFail($id);
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
    public function destroy($id)
    {
        try {
            $kuota = KuotaLembur::findOrFail($id);
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
     * Upload Excel file and import data
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

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $imported = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                // Skip header row
                if ($index === 1) continue;

                $costCenter = trim($row['A'] ?? '');
                $nik = trim($row['B'] ?? '');
                $bulan = intval($row['C'] ?? 0);
                $periodeAwalRaw = trim($row['D'] ?? '');
                $periodeAkhirRaw = trim($row['E'] ?? '');
                $jmlWD = intval($row['F'] ?? 0);
                $jmlWE = intval($row['G'] ?? 0);
                $jmlHN = intval($row['H'] ?? 0);

                // Skip empty rows
                if (empty($costCenter) && empty($nik)) continue;

                // Validate required fields
                if (empty($costCenter) || empty($nik) || empty($periodeAwalRaw) || empty($periodeAkhirRaw)) {
                    $errors[] = "Baris {$index}: Data wajib belum lengkap";
                    continue;
                }

                // Parse dates (support dd/mm/yyyy format)
                try {
                    // Check if it's a numeric date (Excel serial number)
                    if (is_numeric($periodeAwalRaw)) {
                        $periodeAwal = Carbon::instance(
                            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodeAwalRaw)
                        );
                    } else {
                        $periodeAwal = Carbon::createFromFormat('d/m/Y', $periodeAwalRaw);
                    }

                    if (is_numeric($periodeAkhirRaw)) {
                        $periodeAkhir = Carbon::instance(
                            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodeAkhirRaw)
                        );
                    } else {
                        $periodeAkhir = Carbon::createFromFormat('d/m/Y', $periodeAkhirRaw);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Baris {$index}: Format tanggal tidak valid";
                    continue;
                }

                if ($periodeAwal->gt($periodeAkhir)) {
                    $errors[] = "Baris {$index}: Periode tidak valid";
                    continue;
                }

                // Get dokumen_io from project
                $project = DataProyek::where('cost_center', $costCenter)->first();
                $dokIo = $project->dokumen_io ?? null;

                // Auto-calculate bulan if not provided
                if ($bulan <= 0) {
                    $maxBulan = KuotaLembur::where('cost_center', $costCenter)
                        ->where('nik', $nik)
                        ->max('bulan');
                    $bulan = ($maxBulan ?? 0) + 1;
                }

                // Upsert: update if exists, create if not
                KuotaLembur::updateOrCreate(
                    [
                        'cost_center' => $costCenter,
                        'nik' => $nik,
                        'bulan' => $bulan,
                    ],
                    [
                        'dok_io' => $dokIo,
                        'periode_awal' => $periodeAwal->format('Y-m-d'),
                        'periode_akhir' => $periodeAkhir->format('Y-m-d'),
                        'jml_wd' => $jmlWD,
                        'jml_we' => $jmlWE,
                        'jml_hn' => $jmlHN,
                        'status' => null,
                    ]
                );

                $imported++;
            }

            DB::commit();

            $message = "{$imported} data berhasil diimpor.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " baris gagal.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading kuota lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
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
     * Get active karyawan for NIK dropdown
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
}
