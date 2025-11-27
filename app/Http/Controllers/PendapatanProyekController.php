<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendapatanProyek;
use App\Models\BeritaAcaraProject;
use App\Models\HeaderProgressProyek;
use App\Models\HeaderRAB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PendapatanProyekController extends Controller
{
    /**
     * Display main page
     */
    public function index()
    {
        return view('pendapatan.index');
    }

    /**
     * Get Header Progress Proyek list that has at least one approved Berita Acara
     */
    public function getApprovedBeritaAcara(Request $request)
    {
        $search = $request->get('search', '');

        // Get Header Progress Proyek that has at least one approved BA (status = '03')
        $headerProgressList = DB::table('header_progress_proyek as hpp')
            ->select(
                'hpp.norut',
                'hpp.id_project',
                'hp.namaproject',
                'hp.cost_center',
                'hp.no_kontrak',
                'hp.nilai_proyek',
                'hp.start_kontrak',
                'hp.finish_kontrak',
                'k.konsumen',
                'hr.periode_rab',
                'hr.lama'
            )
            ->join('history_proyek as hp', function($join) {
                $join->on('hpp.id_project', '=', 'hp.id_project')
                     ->on('hpp.norut', '=', 'hp.norut');
            })
            ->leftJoin('konsumen as k', 'hp.id_konsumen', '=', 'k.id_konsumen')
            ->leftJoin('header_rab as hr', function($join) {
                $join->on('hp.id_project', '=', 'hr.id_project')
                     ->on('hp.norut', '=', 'hr.norut');
            })
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('berita_acara_project as ba')
                      ->whereColumn('ba.id_project', 'hpp.id_project')
                      ->whereColumn('ba.norut', 'hpp.norut')
                      ->where('ba.status', '03'); // At least one approved BA
            })
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('hpp.id_project', 'LIKE', "%{$search}%")
                          ->orWhere('hp.namaproject', 'LIKE', "%{$search}%")
                          ->orWhere('hp.cost_center', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy('hpp.created_at', 'desc')
            ->limit(50)
            ->get();

        $results = $headerProgressList->map(function($hpp) {
            $costCenter = $hpp->cost_center ?? '-';
            $namaProyek = $hpp->namaproject ?? '-';
            $konsumenNama = $hpp->konsumen ?? '-';
            $nilaiProyek = $hpp->nilai_proyek ?? 0;

            // Format periode Proyek dari History Proyek (start_kontrak - finish_kontrak)
            $periodeProyekMulai = $hpp->start_kontrak ? \Carbon\Carbon::parse($hpp->start_kontrak)->format('d/m/Y') : '-';
            $periodeProyekAkhir = $hpp->finish_kontrak ? \Carbon\Carbon::parse($hpp->finish_kontrak)->format('d/m/Y') : '-';
            $periodeProyek = "{$periodeProyekMulai} - {$periodeProyekAkhir}";

            // Format nilai untuk dropdown text
            $nilaiProyekFormatted = $nilaiProyek ? 'Rp ' . number_format($nilaiProyek, 0, ',', '.') : 'Rp 0';

            // Dropdown text: Cost Center - Nama Proyek - Nilai Proyek - Periode Proyek (dari history_proyek)
            $dropdownText = "{$costCenter} - {$namaProyek} - {$nilaiProyekFormatted} - {$periodeProyek}";

            // Calculate mulai, lama, akhir from Header RAB
            $mulai = $hpp->periode_rab ? \Carbon\Carbon::parse($hpp->periode_rab)->format('d/m/Y') : '-';
            $lama = $hpp->lama ?? '-';
            $akhir = '-';
            if ($hpp->periode_rab && $hpp->lama) {
                $akhir = \Carbon\Carbon::parse($hpp->periode_rab)
                    ->addMonths($hpp->lama - 1)
                    ->endOfMonth()
                    ->format('d/m/Y');
            }

            return [
                'id' => $hpp->id_project . '|' . $hpp->norut,
                'text' => $dropdownText,
                'id_project' => $hpp->id_project,
                'norut' => $hpp->norut,

                // Data dari History Proyek
                'cost_center' => $costCenter,
                'namaproject' => $namaProyek,
                'konsumen_nama' => $konsumenNama,
                'no_kontrak' => $hpp->no_kontrak ?? '-',
                'nilai_proyek' => $nilaiProyek,
                'start_kontrak' => $hpp->start_kontrak ?
                    \Carbon\Carbon::parse($hpp->start_kontrak)->format('d/m/Y') : '-',
                'finish_kontrak' => $hpp->finish_kontrak ?
                    \Carbon\Carbon::parse($hpp->finish_kontrak)->format('d/m/Y') : '-',

                // Data dari Header RAB
                'mulai' => $mulai,
                'lama' => $lama,
                'akhir' => $akhir
            ];
        });

        Log::info('Header Progress Proyek data fetched', [
            'count' => $results->count(),
            'search' => $search
        ]);

        return response()->json($results);
    }

    /**
     * Get pendapatan list for specific BA
     */
    public function getPendapatanByBA(Request $request)
    {
        $idProject = $request->get('id_project');
        $norut = $request->get('norut');

        if (!$idProject || !$norut) {
            return response()->json([
                'success' => false,
                'message' => 'ID Project dan Norut harus diisi'
            ], 400);
        }

        DB::enableQueryLog();

        // Get all pendapatan data for this header progress, ordered by created_at DESC (newest first)
        $pendapatans = PendapatanProyek::where('id_project', $idProject)
            ->where('norut', $norut)
            ->orderBy('created_at', 'desc')
            ->get();

        // Add display numbering (terbaru = nomor terbesar, terlama = 1)
        $totalCount = $pendapatans->count();
        $pendapatans = $pendapatans->map(function($pendapatan, $index) use ($totalCount) {
            $pendapatan->norut_display = $totalCount - $index; // Reverse numbering: newest = highest
            return $pendapatan;
        });

        $queries = DB::getQueryLog();

        Log::info('Pendapatan query result', [
            'id_project' => $idProject,
            'norut' => $norut,
            'query' => $queries,
            'count' => $pendapatans->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $pendapatans
        ]);
    }

    /**
     * Get approved Berita Acara list for specific Header Progress Proyek
     */
    public function getApprovedBAByHeader(Request $request)
    {
        $idProject = $request->get('id_project');
        $norut = $request->get('norut');

        if (!$idProject || !$norut) {
            return response()->json([
                'success' => false,
                'message' => 'ID Project dan Norut harus diisi'
            ], 400);
        }

        // Get all approved BA for this header progress
        $beritaAcaras = DB::table('berita_acara_project')
            ->where('id_project', $idProject)
            ->where('norut', $norut)
            ->where('status', '03') // Approved only
            ->orderBy('created_at', 'desc')
            ->get();

        $results = $beritaAcaras->map(function($ba) {
            $periodeBaMulai = $ba->periode_mulai ? \Carbon\Carbon::parse($ba->periode_mulai)->format('d/m/Y') : '-';
            $periodeBAkhir = $ba->periode_akhir ? \Carbon\Carbon::parse($ba->periode_akhir)->format('d/m/Y') : '-';

            return [
                'no_ba' => $ba->no_ba,
                'desc' => $ba->desc ?? '-',
                'periode_mulai' => $periodeBaMulai,
                'periode_akhir' => $periodeBAkhir,
                'nilai_ba' => $ba->nilai_ba
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Store new pendapatan
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Super Admin', 'Project Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk membuat Pendapatan'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'no_ba' => 'nullable|string|max:9',
            'tanggal' => 'required|date',
            'no_dokumen' => 'required|string|max:100',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_pendapatan' => 'nullable|numeric|min:0',
            'file_ba' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        try {
            DB::beginTransaction();

            // Verify Header Progress exists and has at least one approved BA
            $approvedBA = DB::table('berita_acara_project')
                ->where('id_project', $request->id_project)
                ->where('norut', $request->norut)
                ->where('status', '03')
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$approvedBA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada Berita Acara yang disetujui untuk proyek ini'
                ], 404);
            }

            // Use provided no_ba or use first approved BA
            $noBA = $request->no_ba ?? $approvedBA->no_ba;

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file_ba')) {
                $file = $request->file('file_ba');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('pendapatan_ba', $fileName, 'public');
            }

            // Generate no_pendapatan (will be auto-generated in boot method)
            $pendapatan = PendapatanProyek::create([
                'norut' => $request->norut,
                'id_project' => $request->id_project,
                'no_ba' => $noBA,
                'tanggal' => $request->tanggal,
                'no_dokumen' => $request->no_dokumen,
                'periode_mulai' => $request->periode_mulai,
                'periode_akhir' => $request->periode_akhir,
                'nilai_pendapatan' => $request->nilai_pendapatan,
                'file_ba' => $filePath
            ]);

            DB::commit();

            Log::info('Pendapatan created', [
                'id_project' => $request->id_project,
                'norut' => $request->norut,
                'no_ba' => $noBA,
                'no_pendapatan' => $pendapatan->no_pendapatan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendapatan berhasil dibuat',
                'data' => $pendapatan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Pendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Pendapatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update pendapatan
     */
    public function update(Request $request, $noPendapatan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['Super Admin', 'Project Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah Pendapatan'
            ], 403);
        }

        $request->validate([
            'id_project' => 'required|string|max:10',
            'norut' => 'required|integer',
            'no_ba' => 'nullable|string|max:9',
            'no_dokumen' => 'required|string|max:100',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_pendapatan' => 'nullable|numeric|min:0',
            'file_ba' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        try {
            DB::beginTransaction();

            // Find by composite key (no_ba can be from existing record)
            $pendapatan = PendapatanProyek::where('norut', $request->norut)
                ->where('id_project', $request->id_project)
                ->where('no_pendapatan', $noPendapatan)
                ->firstOrFail();

            $updateData = [
                'no_dokumen' => $request->no_dokumen,
                'periode_mulai' => $request->periode_mulai,
                'periode_akhir' => $request->periode_akhir,
                'nilai_pendapatan' => $request->nilai_pendapatan
            ];

            // Handle file upload
            if ($request->hasFile('file_ba')) {
                // Delete old file if exists
                if ($pendapatan->file_ba) {
                    Storage::disk('public')->delete($pendapatan->file_ba);
                }

                $file = $request->file('file_ba');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('pendapatan_ba', $fileName, 'public');
                $updateData['file_ba'] = $filePath;
            }

            $pendapatan->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pendapatan berhasil diperbarui',
                'data' => $pendapatan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Pendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Pendapatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete pendapatan
     */
    public function destroy(Request $request, $noPendapatan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Super Admin yang dapat menghapus Pendapatan'
            ], 403);
        }

        try {
            $idProject = $request->get('id_project');
            $norut = $request->get('norut');

            // Find by composite key (no longer need no_ba in WHERE clause)
            $pendapatan = PendapatanProyek::where('norut', $norut)
                ->where('id_project', $idProject)
                ->where('no_pendapatan', $noPendapatan)
                ->firstOrFail();

            // Delete file if exists
            if ($pendapatan->file_ba) {
                Storage::disk('public')->delete($pendapatan->file_ba);
            }

            $pendapatan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pendapatan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting Pendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Pendapatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file BA
     */
    public function download($noPendapatan, Request $request)
    {
        $idProject = $request->get('id_project');
        $norut = $request->get('norut');

        $pendapatan = PendapatanProyek::where('norut', $norut)
            ->where('id_project', $idProject)
            ->where('no_pendapatan', $noPendapatan)
            ->firstOrFail();

        if (!$pendapatan->file_ba || !Storage::disk('public')->exists($pendapatan->file_ba)) {
            abort(404, 'File tidak ditemukan');
        }

        $filePath = Storage::disk('public')->path($pendapatan->file_ba);
        $fileName = basename($pendapatan->file_ba);

        return response()->download($filePath, $fileName);
    }
}
