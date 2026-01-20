<?php

namespace App\Http\Controllers;

use App\Models\Plsap;
use App\Services\SAPImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SAPImportController extends Controller
{
    protected SAPImportService $importService;

    public function __construct(SAPImportService $importService)
    {
        $this->importService = $importService;
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
     * Upload dan import file CSV via form upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200'
        ]);

        try {
            $file = $request->file('csv_file');
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = $file->storeAs('temp/sap', $filename, 'local');
            $fullPath = storage_path('app/' . $path);

            Log::info('SAP Upload - File saved to: ' . $fullPath);

            $force = $request->boolean('force', false);
            $result = $this->importService->importFromCSV($fullPath, $force);

            // Hapus file temporary
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            $httpStatus = $result['success'] ? 200 : (($result['error_type'] ?? '') === 'DUPLICATE_FILE' ? 409 : 422);

            return response()->json($result, $httpStatus);

        } catch (\Exception $e) {
            Log::error('SAP Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error_type' => 'UPLOAD_ERROR',
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import dari path lokal
     */
    public function importLocal(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'force' => 'nullable'
        ]);

        $filePath = $request->input('file_path');
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

        Log::info('SAP Import Local - Path: ' . $filePath);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'error_type' => 'FILE_NOT_FOUND',
                'message' => "File tidak ditemukan: {$filePath}"
            ], 404);
        }

        if (!is_readable($filePath)) {
            return response()->json([
                'success' => false,
                'error_type' => 'FILE_NOT_READABLE',
                'message' => "File tidak dapat dibaca: {$filePath}"
            ], 403);
        }

        // Check force parameter (bisa dari checkbox atau boolean)
        $force = $request->boolean('force', false) || $request->input('force') === 'on';

        $result = $this->importService->importFromCSV($filePath, $force);

        $httpStatus = $result['success'] ? 200 : (($result['error_type'] ?? '') === 'DUPLICATE_FILE' ? 409 : 422);

        return response()->json($result, $httpStatus);
    }

    /**
     * Force import
     */
    public function forceImport(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string'
        ]);

        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $request->input('file_path'));

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => "File tidak ditemukan: {$filePath}"
            ], 404);
        }

        $result = $this->importService->forceImportFromCSV($filePath);

        return response()->json($result);
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
}