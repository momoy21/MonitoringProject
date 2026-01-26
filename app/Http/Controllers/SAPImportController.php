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

        $stats = [
            'total_records' => Plsap::count(),
            'total_amount' => Plsap::sum('amount_local'),
            'unique_projects' => Plsap::distinct('cc_projek')->count('cc_projek'),
            'last_import' => Plsap::max('imported_at'),
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
     */
    public function deleteBySource(Request $request)
    {
        $request->validate([
            'source_file' => 'required|string'
        ]);

        try {
            $sourceFile = $request->source_file;
            $count = Plsap::where('source_file', $sourceFile)->count();

            Plsap::where('source_file', $sourceFile)->delete();

            // Hapus dari history juga
            try {
                DB::table('sap_import_history')->where('filename', $sourceFile)->delete();
            } catch (\Exception $e) {}

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} record dari {$sourceFile}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of source files
     */
    public function getSourceFiles()
    {
        $files = Plsap::select('source_file')
            ->selectRaw('COUNT(*) as record_count')
            ->selectRaw('SUM(amount_local) as total_amount')
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
     * Import file from FTP
     */
    public function importFromFtp(Request $request)
    {
        $request->validate([
            'ftp_path' => 'required|string',
            'force' => 'nullable'
        ]);

        $ftpPath = $request->input('ftp_path');
        $force = $request->boolean('force', false) || $request->input('force') === 'on';

        Log::info('SAP Import FTP - Path: ' . $ftpPath, ['force' => $force]);

        try {
            // Step 1: Download file dari FTP ke temp folder
            $downloadResult = $this->ftpService->downloadToTemp($ftpPath);

            if (!$downloadResult['success']) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'FTP_DOWNLOAD_ERROR',
                    'message' => $downloadResult['message']
                ], 422);
            }

            $tempPath = $downloadResult['temp_path'];
            $filename = $downloadResult['filename'];

            // Step 2: Import file menggunakan SAPImportService
            // Pass original filename agar source_file tercatat dengan nama asli
            $result = $this->importService->importFromCSV($tempPath, $force, $filename);

            // Step 3: Cleanup temp file
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }

            // Step 4: Move file di FTP berdasarkan hasil import
            $this->moveFileAfterImport($ftpPath, $filename, $result);

            $httpStatus = $result['success'] ? 200 : (($result['error_type'] ?? '') === 'DUPLICATE_FILE' ? 409 : 422);

            return response()->json($result, $httpStatus);

        } catch (\Exception $e) {
            Log::error('SAP FTP Import Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error_type' => 'FTP_IMPORT_ERROR',
                'message' => 'Gagal import dari FTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move file di FTP setelah import berdasarkan hasil
     * - Success → /Processed/
     * - Error/Rejected → /Error/
     */
    protected function moveFileAfterImport(string $ftpPath, string $filename, array $result): void
    {
        try {
            $destinationFolder = $result['success'] 
                ? $this->ftpService->getProcessedDirectory() 
                : $this->ftpService->getErrorDirectory();
            $destinationPath = $destinationFolder . '/' . $filename;

            $moveResult = $this->ftpService->moveFile($ftpPath, $destinationPath);

            if ($moveResult['success']) {
                Log::info('File moved after import', [
                    'file' => $filename,
                    'destination' => $destinationFolder,
                    'import_success' => $result['success']
                ]);
            } else {
                Log::warning('Failed to move file after import', [
                    'file' => $filename,
                    'destination' => $destinationFolder,
                    'error' => $moveResult['message']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error moving file after import', [
                'file' => $filename,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get FTP directories
     */
    public function getFtpDirectories(Request $request)
    {
        $directory = $request->get('directory', '/');
        $result = $this->ftpService->listDirectories($directory);

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
}