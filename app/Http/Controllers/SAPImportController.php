<?php

namespace App\Http\Controllers;

use App\Models\Plsap;
use App\Services\SAPImportService;
use App\Services\FtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SAPImportController extends Controller
{
    protected SAPImportService $importService;
    protected FtpService $ftpService;

    public function __construct(SAPImportService $importService, FtpService $ftpService)
    {
        $this->importService = $importService;
        $this->ftpService = $ftpService;
    }

    /**
     * Tampilkan halaman import SAP
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $search = $request->get('search', '');

        $query = Plsap::query()
            ->orderBy('imported_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('internal_order', 'like', "%{$search}%")
                    ->orWhere('cc_projek', 'like', "%{$search}%")
                    ->orWhere('description_io', 'like', "%{$search}%")
                    ->orWhere('cost_element', 'like', "%{$search}%")
                    ->orWhere('description_ce', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($perPage);

        // Enhanced statistics
        $totalAmount = Plsap::sum('amount_local');
        $totalPengeluaran = Plsap::where('amount_local', '>', 0)->sum('amount_local'); // Positif = Biaya
        $totalPendapatan = abs(Plsap::where('amount_local', '<', 0)->sum('amount_local')); // Negatif = Pendapatan
        
        // Mapping statistics - Aktual Biaya
        $totalMapped = \App\Models\AktualBiaya::count();
        $totalUnmapped = Plsap::whereNotIn('id', \App\Models\AktualBiaya::pluck('plsap_id'))->count();
        
        $stats = [
            'total_records' => Plsap::count(),
            'total_amount' => $totalAmount,
            'total_pengeluaran' => $totalPengeluaran,
            'total_pendapatan' => $totalPendapatan,
            'net_amount' => $totalPengeluaran - $totalPendapatan, // Positif = Rugi, Negatif = Untung
            'unique_projects' => Plsap::distinct('cc_projek')->count('cc_projek'),
            'unique_files' => Plsap::distinct('source_file')->count('source_file'),
            'last_import' => Plsap::max('imported_at'),
            // Mapping stats
            'total_mapped' => $totalMapped,
            'total_unmapped' => $totalUnmapped,
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
                'stats' => $stats
            ]);
        }

        return view('sap.index', compact('data', 'stats'));
    }

    /**
     * Get error logs
     */
    public function getErrorLogs(Request $request)
    {
        $date = $request->get('date', date('Ymd'));
        $logs = $this->importService->getErrorLogs($date);

        return response()->json([
            'success' => true,
            'date' => $date,
            'logs' => $logs
        ]);
    }

    /**
     * Get import logs
     */
    public function getImportLogs(Request $request)
    {
        $date = $request->get('date', date('Ymd'));
        $logs = $this->importService->getImportLogs($date);

        return response()->json([
            'success' => true,
            'date' => $date,
            'logs' => $logs
        ]);
    }

    /**
     * Get auto import logs (from scheduler)
     */
    public function getAutoImportLogs()
    {
        $logFile = storage_path('logs/sap-auto-import.log');

        if (File::exists($logFile)) {
            // Read last 500 lines
            $content = File::get($logFile);
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -500);
            $logs = implode("\n", $lastLines);
        } else {
            $logs = "Auto import log belum tersedia.\nScheduler belum pernah dijalankan atau log kosong.";
        }

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Hapus semua data SAP
     */
    public function truncate()
    {
        try {
            $count = Plsap::count();
            Plsap::truncate();

            // Clear history juga
            try {
                DB::table('sap_import_history')->truncate();
            } catch (\Exception $e) {}

            Log::info('SAP Truncate - Deleted ' . $count . ' records');

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} record"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus data berdasarkan source file
     * Menghapus dari: aktual_biaya, plsap, dan sap_import_history
     */
    public function deleteBySource(Request $request)
    {
        $request->validate([
            'source_file' => 'required|string'
        ]);

        try {
            $sourceFile = $request->source_file;
            
            // Ambil ID plsap yang akan dihapus
            $plsapIds = Plsap::where('source_file', $sourceFile)->pluck('id')->toArray();
            $count = count($plsapIds);

            // 1. Hapus aktual_biaya yang terkait terlebih dahulu
            $deletedAktualBiaya = 0;
            if (!empty($plsapIds)) {
                $deletedAktualBiaya = \App\Models\AktualBiaya::whereIn('plsap_id', $plsapIds)->delete();
            }

            // 2. Hapus dari plsap
            Plsap::where('source_file', $sourceFile)->delete();

            // 3. Hapus dari history juga
            try {
                DB::table('sap_import_history')->where('filename', $sourceFile)->delete();
            } catch (\Exception $e) {}

            Log::info("SAP Delete by Source: {$sourceFile}", [
                'plsap_deleted' => $count,
                'aktual_biaya_deleted' => $deletedAktualBiaya
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} record SAP dan {$deletedAktualBiaya} record aktual biaya dari {$sourceFile}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of source files with biaya/pendapatan breakdown
     */
    public function getSourceFiles()
    {
        $files = Plsap::select('source_file')
            ->selectRaw('COUNT(*) as record_count')
            ->selectRaw('SUM(amount_local) as total_amount')
            ->selectRaw('SUM(CASE WHEN amount_local > 0 THEN amount_local ELSE 0 END) as total_biaya')
            ->selectRaw('ABS(SUM(CASE WHEN amount_local < 0 THEN amount_local ELSE 0 END)) as total_pendapatan')
            ->selectRaw('MAX(imported_at) as imported_at')
            ->groupBy('source_file')
            ->orderBy('imported_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    // ========================================================================
    // FTP METHODS
    // ========================================================================

    /**
     * Test FTP connection
     */
    public function testFtpConnection()
    {
        $result = $this->ftpService->testConnection();
        $connectionInfo = $this->ftpService->getConnectionInfo();

        return response()->json([
            ...$result,
            'connection_info' => $connectionInfo
        ], $result['success'] ? 200 : 500);
    }

    /**
     * List files from FTP server
     */
    public function listFtpFiles(Request $request)
    {
        // Default ke source directory dari env (FTP_SOURCE_DIR)
        $directory = $request->get('directory', $this->ftpService->getSourceDirectory());
        
        $result = $this->ftpService->listCsvFiles($directory);
        
        // Add import status for each file
        if ($result['success'] && !empty($result['files'])) {
            foreach ($result['files'] as &$file) {
                $file['already_imported'] = Plsap::where('source_file', $file['name'])->exists();
            }
        }

        return response()->json($result);
    }



    /**
     * Get FTP connection info
     */
    public function getFtpInfo()
    {
        $info = $this->ftpService->getConnectionInfo();
        $testResult = $this->ftpService->testConnection();

        return response()->json([
            'success' => true,
            'info' => $info,
            'status' => $testResult['success'] ? 'connected' : 'disconnected',
            'status_message' => $testResult['message']
        ]);
    }

    /**
     * Re-map PLSAP data ke Aktual Biaya
     */
    public function remapToAktualBiaya(Request $request)
    {
        try {
            $force = $request->boolean('force', false);
            $dryRun = $request->boolean('dry_run', false);

            $aktualBiayaService = app(\App\Services\AktualBiayaService::class);

            // Jika force, hapus existing mappings terlebih dahulu
            if ($force && !$dryRun) {
                $deleted = \App\Models\AktualBiaya::whereNotNull('plsap_id')->delete();
                Log::info("SAP Remap: Deleted {$deleted} existing aktual_biaya records");
            }

            // Jalankan mapping
            $result = $aktualBiayaService->processMapping(null, $force);

            // Hitung statistik terbaru
            $totalMapped = \App\Models\AktualBiaya::whereNotNull('plsap_id')->count();
            $totalUnmapped = Plsap::whereNotIn('id', \App\Models\AktualBiaya::pluck('plsap_id'))->count();

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Mapping selesai: %d berhasil, %d dilewati, %d tidak ada mapping',
                    $result['total_mapped'],
                    $result['total_skipped'],
                    $result['total_unmapped']
                ),
                'result' => $result,
                'stats' => [
                    'total_mapped' => $totalMapped,
                    'total_unmapped' => $totalUnmapped,
                ],
                'unmapped_cost_elements' => array_slice($result['unmapped_cost_elements'] ?? [], 0, 10),
            ]);

        } catch (\Exception $e) {
            Log::error('SAP Remap Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan remap: ' . $e->getMessage(),
            ], 500);
        }
    }
}