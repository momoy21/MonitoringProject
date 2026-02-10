<?php

namespace App\Http\Controllers;

use App\Models\KuotaLembur;
use App\Models\LemburInterfaceLog;
use App\Services\LemburExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LemburInterfaceController extends Controller
{
    protected LemburExportService $exportService;

    public function __construct(LemburExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Display the lembur interface page
     */
    public function index(Request $request)
    {
        // Get interface logs for history
        $logs = LemburInterfaceLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Get statistics
        $stats = [
            'total_pending' => KuotaLembur::whereNull('status')->count(),
            'total_synced' => KuotaLembur::where('status', 'F')->count(),
            'total_logs' => LemburInterfaceLog::count(),
            'success_logs' => LemburInterfaceLog::where('status', 'success')->count(),
            'failed_logs' => LemburInterfaceLog::where('status', 'failed')->count(),
        ];

        return view('lembur.index', compact('logs', 'stats'));
    }

    /**
     * Submit - Fetch data based on periode
     */
    public function submit(Request $request)
    {
        $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ], [
            'periode_awal.required' => 'Periode Awal harus diisi',
            'periode_akhir.required' => 'Periode Akhir harus diisi',
            'periode_akhir.after_or_equal' => 'Periode Akhir harus sama atau setelah Periode Awal',
        ]);

        $periodeAwal = $request->input('periode_awal');
        $periodeAkhir = $request->input('periode_akhir');

        Log::info('Lembur Interface Submit Request', [
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
        ]);

        try {
            // First check total data available
            $totalData = KuotaLembur::whereNull('status')->count();
            Log::info('Total KuotaLembur with null status: ' . $totalData);

            // Query data from kuota_lembur with status null
            $data = KuotaLembur::with('karyawan', 'proyek')
                ->where('periode_awal', '>=', $periodeAwal)
                ->where('periode_akhir', '<=', $periodeAkhir)
                ->whereNull('status')
                ->orderBy('nik')
                ->orderBy('bulan')
                ->get();

            Log::info('Query result count: ' . $data->count());

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Data tidak ditemukan untuk periode {$periodeAwal} s/d {$periodeAkhir} dengan status belum terkirim. Total data pending: {$totalData}",
                ]);
            }

            // Format data for display
            $formattedData = $data->map(function ($item) {
                return [
                    'nik' => $item->nik,
                    'nama' => $item->karyawan?->nama ?? '-',
                    'cost_center' => $item->cost_center,
                    'dok_io' => $item->dok_io,
                    'bulan' => $item->bulan,
                    'periode_awal' => $item->periode_awal->format('Y-m-d'),
                    'periode_akhir' => $item->periode_akhir->format('Y-m-d'),
                    'jml_wd' => $item->jml_wd,
                    'jml_we' => $item->jml_we,
                    'jml_hn' => $item->jml_hn,
                    'status' => $item->status ?? 'Belum Terkirim',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditemukan',
                'data' => $formattedData,
                'total' => $data->count(),
                'periode_awal' => $periodeAwal,
                'periode_akhir' => $periodeAkhir,
            ]);

        } catch (\Exception $e) {
            Log::error('Lembur Interface Submit Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync - Export to CSV and upload to FTP
     */
    public function sync(Request $request)
    {
        $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ]);

        $periodeAwal = $request->input('periode_awal');
        $periodeAkhir = $request->input('periode_akhir');

        try {
            DB::beginTransaction();

            // Get data to export
            $data = KuotaLembur::with('karyawan')
                ->where('periode_awal', '>=', $periodeAwal)
                ->where('periode_akhir', '<=', $periodeAkhir)
                ->whereNull('status')
                ->orderBy('nik')
                ->orderBy('bulan')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk disinkronkan.',
                ]);
            }

            // Export to CSV and upload to FTP
            $result = $this->exportService->exportToFtp($data, $periodeAwal, $periodeAkhir);

            if (!$result['success']) {
                DB::rollBack();
                
                // Log failed attempt
                $this->createLog($periodeAwal, $periodeAkhir, null, $data->count(), 'failed', $result['message']);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }

            // Update status to 'F' for all synced records
            KuotaLembur::where('periode_awal', '>=', $periodeAwal)
                ->where('periode_akhir', '<=', $periodeAkhir)
                ->whereNull('status')
                ->update(['status' => 'F', 'updated_at' => now()]);

            // Log success
            $this->createLog($periodeAwal, $periodeAkhir, $result['filename'], $data->count(), 'success', $result['message']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'filename' => $result['filename'],
                'total_records' => $data->count(),
                'ftp_path' => $result['path'] ?? null,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Lembur Interface Sync Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Log failed attempt
            $this->createLog($periodeAwal, $periodeAkhir, null, 0, 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get interface logs
     */
    public function getLogs(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        
        $logs = LemburInterfaceLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Test FTP connection for Lembur
     */
    public function testFtp()
    {
        $result = $this->exportService->testFtpConnection();
        
        return response()->json($result);
    }

    /**
     * Create interface log
     */
    protected function createLog(
        string $periodeAwal,
        string $periodeAkhir,
        ?string $filename,
        int $totalRecords,
        string $status,
        ?string $message = null
    ): LemburInterfaceLog {
        return LemburInterfaceLog::create([
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'filename' => $filename,
            'total_records' => $totalRecords,
            'status' => $status,
            'message' => $message,
            'created_by' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }
}
