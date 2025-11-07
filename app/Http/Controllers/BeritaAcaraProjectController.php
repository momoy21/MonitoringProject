<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcaraProject;
use App\Models\HeaderProgressProyek;
use App\Models\HistoryProyek;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BeritaAcaraProjectController extends Controller
{
    /**
     * Get Berita Acara list for a specific project (via AJAX)
     */
    public function getBeritaAcaraByProject(Request $request)
    {
        $idProject = $request->get('id_project');
        $norut = $request->get('norut');


        if (!$idProject || !$norut) {
            return response()->json([
                'success' => false,
                'message' => 'ID Project dan Norut harus diisi'
            ], 400);
        }

        // Enable query log for debugging
        DB::enableQueryLog();

        // FIXED: Filter berdasarkan id_project DAN norut dari history proyek
        $beritaAcaras = BeritaAcaraProject::where('id_project', $idProject)
            ->where('norut', $norut) // CRITICAL: Filter by history proyek norut
            ->orderBy('created_at', 'desc')
            ->get();

        // Add display numbering (terlama = 1, terbaru = highest)
        $totalCount = $beritaAcaras->count();
        $beritaAcaras = $beritaAcaras->map(function($ba, $index) use ($totalCount) {
            $ba->norut_display = $totalCount - $index; // Reverse numbering
            return $ba;
        });

        // Get the executed query
        $queries = DB::getQueryLog();

        Log::info('BeritaAcara query result', [
            'id_project' => $idProject,
            'norut' => $norut,
            'query' => $queries,
            'count' => $beritaAcaras->count(),
            'note' => 'Filtered by id_project AND norut from history_proyek',
            'data' => $beritaAcaras->toArray()
        ]);

        return response()->json([
            'success' => true,
            'data' => $beritaAcaras
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is Super Admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat membuat Berita Acara'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'desc' => 'nullable|string',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_ba' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:01,02,03,04'
        ]);

        try {
            DB::beginTransaction();

            // Verify that history_proyek exists with this id_project and norut
            $historyProyek = HistoryProyek::where('id_project', $request->id_project)
                ->where('norut', $request->norut)
                ->first();

            if (!$historyProyek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data History Proyek tidak ditemukan'
                ], 404);
            }

            // Generate no_ba (Format: BA + YYYY + sequential number per project+norut)
            $noBA = $this->generateNoBA($request->id_project, $request->norut);

            // CRITICAL: Use norut from history_proyek (request->norut)
            $beritaAcara = BeritaAcaraProject::create([
                'norut' => $request->norut,  // Use norut from history_proyek
                'id_project' => $request->id_project,
                'no_ba' => $noBA,
                'desc' => $request->desc,
                'periode_mulai' => $request->periode_mulai,
                'periode_akhir' => $request->periode_akhir,
                'nilai_ba' => $request->nilai_ba,
                'status' => $request->status ?? '01'
            ]);

            DB::commit();

            Log::info('Berita Acara created', [
                'id_project' => $request->id_project,
                'norut' => $request->norut,
                'no_ba' => $noBA
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara berhasil dibuat',
                'data' => $beritaAcara
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Berita Acara: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Berita Acara: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $noBA)
    {
        // Check if user is Super Admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat mengubah Berita Acara'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'desc' => 'nullable|string',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_ba' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:01,02,03,04'
        ]);

        try {
            DB::beginTransaction();

            // Find by composite key: norut + id_project + no_ba
            $beritaAcara = BeritaAcaraProject::where('norut', $request->norut)
                ->where('id_project', $request->id_project)
                ->where('no_ba', $noBA)
                ->firstOrFail();

            $beritaAcara->update([
                'desc' => $request->desc,
                'periode_mulai' => $request->periode_mulai,
                'periode_akhir' => $request->periode_akhir,
                'nilai_ba' => $request->nilai_ba,
                'status' => $request->status
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara berhasil diperbarui',
                'data' => $beritaAcara
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Berita Acara: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Berita Acara: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status only (can be called from table directly)
     */
    public function updateStatus(Request $request)
    {
        // Check if user is Super Admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat mengubah status'
            ], 403);
        }

        $request->validate([
            'no_ba' => 'required|string',
            'id_project' => 'required|string',
            'norut' => 'required|integer',
            'status' => 'required|in:01,02,03,04'
        ]);

        try {
            // Find by composite key: norut + id_project + no_ba
            $beritaAcara = BeritaAcaraProject::where('norut', $request->norut)
                ->where('id_project', $request->id_project)
                ->where('no_ba', $request->no_ba)
                ->firstOrFail();

            $beritaAcara->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui',
                'data' => $beritaAcara
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $noBA)
    {
        // Check if user is Super Admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat menghapus Berita Acara'
            ], 403);
        }

        try {
            $idProject = $request->get('id_project');
            $norut = $request->get('norut');

            // Find by composite key: norut + id_project + no_ba
            $beritaAcara = BeritaAcaraProject::where('norut', $norut)
                ->where('id_project', $idProject)
                ->where('no_ba', $noBA)
                ->firstOrFail();

            $beritaAcara->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting Berita Acara: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Berita Acara: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate no_ba (Format: BA + YYYY + 001, sequential per project+norut)
     */
    private function generateNoBA($idProject, $norut)
    {
        $year = date('Y');
        $prefix = 'BA' . $year;

        // Count existing BA for this project+norut combination
        $lastBA = BeritaAcaraProject::where('id_project', $idProject)
            ->where('norut', $norut)
            ->where('no_ba', 'LIKE', $prefix . '%')
            ->orderBy('no_ba', 'desc')
            ->first();

        if ($lastBA) {
            $lastSequence = (int) substr($lastBA->no_ba, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix . $newSequence;
    }
}
