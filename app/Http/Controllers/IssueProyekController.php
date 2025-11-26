<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IssueProyek;
use App\Models\HistoryProyek;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IssueProyekController extends Controller
{
    /**
     * Get Issue list for a specific project (via AJAX)
     */
    public function getIssueByProject(Request $request)
    {
        $idProject = $request->get('id_project');
        $norut = $request->get('norut');

        if (!$idProject || !$norut) {
            return response()->json([
                'success' => false,
                'message' => 'ID Project dan Norut harus diisi'
            ], 400);
        }

        $issues = IssueProyek::where('id_project', $idProject)
            ->where('norut', $norut)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $issues
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is Super Admin or PM
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Super Admin', 'Project Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'tanggal' => 'required|date',
            'issue' => 'nullable|string',
            'mitigasi' => 'nullable|string',
            'status' => 'nullable|in:O,C'
        ]);

        try {
            DB::beginTransaction();

            // Verify history_proyek exists
            $historyProyek = HistoryProyek::where('id_project', $request->id_project)
                ->where('norut', $request->norut)
                ->firstOrFail();

            // Generate no_issue
            $noIssue = $this->generateNoIssue($request->id_project, $request->norut);

            // CRITICAL: Format tanggal dengan benar (date only, no time)
            $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

            $issue = IssueProyek::create([
                'norut' => $request->norut,
                'id_project' => $request->id_project,
                'no_issue' => $noIssue,
                'tanggal' => $tanggal,
                'issue' => $request->issue ?: 'Tidak ada issue',
                'mitigasi' => $request->mitigasi ?: 'Tidak ada mitigasi',
                'status' => $request->status ?? 'O'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Issue berhasil ditambahkan',
                'data' => $issue
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Issue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Issue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * CRITICAL FIX: Mengikuti logic Berita Acara - update data yang sudah ada, JANGAN buat data baru
     */
    public function update(Request $request, $noIssue)
    {
        // Check if user is Super Admin or PM
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Super Admin', 'Project Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'issue' => 'nullable|string',
            'mitigasi' => 'nullable|string',
            'status' => 'nullable|in:O,C'
        ]);

        try {
            DB::beginTransaction();

            // CRITICAL: Find existing issue by composite key
            // Query harus exact match dengan ketiga key
            $issue = IssueProyek::where('no_issue', $noIssue)
                ->where('id_project', $request->id_project)
                ->where('norut', $request->norut)
                ->firstOrFail();

            // CRITICAL: Update data yang sudah ada (JANGAN buat record baru)
            // Tanggal TIDAK diupdate karena tanggal tidak boleh berubah saat edit
            $issue->issue = $request->issue ?: 'Tidak ada issue';
            $issue->mitigasi = $request->mitigasi ?: 'Tidak ada mitigasi';
            $issue->status = $request->status;
            $issue->save();

            DB::commit();

            Log::info('Issue updated successfully', [
                'no_issue' => $noIssue,
                'id_project' => $request->id_project,
                'norut' => $request->norut,
                'note' => 'Data existing berhasil diupdate, TIDAK membuat data baru'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Issue berhasil diperbarui',
                'data' => $issue
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Issue: ' . $e->getMessage(), [
                'no_issue' => $noIssue,
                'id_project' => $request->id_project,
                'norut' => $request->norut
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Issue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status only
     */
    public function updateStatus(Request $request)
    {
        // Check if user is Super Admin or PM
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Super Admin', 'Project Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'no_issue' => 'required|string',
            'id_project' => 'required|string',
            'norut' => 'required|integer',
            'status' => 'required|in:O,C'
        ]);

        try {
            $issue = IssueProyek::where('norut', $request->norut)
                ->where('id_project', $request->id_project)
                ->where('no_issue', $request->no_issue)
                ->firstOrFail();

            $issue->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui',
                'data' => $issue
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
    public function destroy(Request $request, $noIssue)
    {
        // Check if user is Super Admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat menghapus Issue'
            ], 403);
        }

        try {
            $idProject = $request->get('id_project');
            $norut = $request->get('norut');

            // Find by composite key
            $issue = IssueProyek::where('norut', $norut)
                ->where('id_project', $idProject)
                ->where('no_issue', $noIssue)
                ->firstOrFail();

            $issue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Issue berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting Issue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Issue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate no_issue (Format: IS + 001, sequential per project+norut)
     */
    private function generateNoIssue($idProject, $norut)
    {
        $prefix = 'IS';

        $lastIssue = IssueProyek::where('id_project', $idProject)
            ->where('norut', $norut)
            ->orderBy('no_issue', 'desc')
            ->first();

        if ($lastIssue) {
            $lastSequence = intval(substr($lastIssue->no_issue, 2));
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix . $newSequence;
    }
}
