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
     * Get approved Berita Acara list for pendapatan dropdown
     */
    public function getApprovedBeritaAcara(Request $request)
    {
        $search = $request->get('search', '');

        // Get only approved BA (status = '03') with manual joins
        $beritaAcaras = DB::table('berita_acara_project as ba')
            ->select(
                'ba.norut',
                'ba.id_project',
                'ba.no_ba',
                'ba.desc',
                'ba.periode_mulai',
                'ba.periode_akhir',
                'ba.nilai_ba',
                'ba.status',
                'ba.created_at',
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
                $join->on('ba.id_project', '=', 'hp.id_project')
                     ->on('ba.norut', '=', 'hp.norut');
            })
            ->leftJoin('konsumen as k', 'hp.id_konsumen', '=', 'k.id_konsumen')
            ->leftJoin('header_rab as hr', function($join) {
                $join->on('hp.id_project', '=', 'hr.id_project')
                     ->on('hp.norut', '=', 'hr.norut');
            })
            ->where('ba.status', '03')
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('ba.no_ba', 'LIKE', "%{$search}%")
                          ->orWhere('ba.id_project', 'LIKE', "%{$search}%")
                          ->orWhere('hp.namaproject', 'LIKE', "%{$search}%")
                          ->orWhere('hp.cost_center', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy('ba.created_at', 'desc')
            ->limit(50)
            ->get();

        $results = $beritaAcaras->map(function($ba) {
            $costCenter = $ba->cost_center ?? '-';
            $namaProyek = $ba->namaproject ?? '-';
            $konsumenNama = $ba->konsumen ?? '-';
            $nilaiProyek = $ba->nilai_proyek ?? 0;
            $keteranganBA = $ba->desc ?? '-';

            // Format periode Proyek dari History Proyek (start_kontrak - finish_kontrak)
            $periodeProyekMulai = $ba->start_kontrak ? \Carbon\Carbon::parse($ba->start_kontrak)->format('d/m/Y') : '-';
            $periodeProyekAkhir = $ba->finish_kontrak ? \Carbon\Carbon::parse($ba->finish_kontrak)->format('d/m/Y') : '-';
            $periodeProyek = "{$periodeProyekMulai} - {$periodeProyekAkhir}";

            // Format periode BA (untuk dropdown)
            $periodeBaMulai = $ba->periode_mulai ? \Carbon\Carbon::parse($ba->periode_mulai)->format('d/m/Y') : '-';
            $periodeBAkhir = $ba->periode_akhir ? \Carbon\Carbon::parse($ba->periode_akhir)->format('d/m/Y') : '-';
            $periodeBA = ($periodeBaMulai !== '-' && $periodeBAkhir !== '-') ? "{$periodeBaMulai} - {$periodeBAkhir}" : '-';

            // Format nilai BA untuk dropdown text
            $nilaiBAFormatted = $ba->nilai_ba ? 'Rp ' . number_format($ba->nilai_ba, 0, ',', '.') : 'Rp 0';

            // Dropdown text: Cost Center - Keterangan BA - Periode BA - Nilai BA
            $dropdownText = "{$costCenter} - {$keteranganBA} - {$periodeBA} - {$nilaiBAFormatted}";

            // Calculate mulai, lama, akhir from Header RAB
            $mulai = $ba->periode_rab ? \Carbon\Carbon::parse($ba->periode_rab)->format('d/m/Y') : '-';
            $lama = $ba->lama ?? '-';
            $akhir = '-';
            if ($ba->periode_rab && $ba->lama) {
                $akhir = \Carbon\Carbon::parse($ba->periode_rab)
                    ->addMonths($ba->lama - 1)
                    ->endOfMonth()
                    ->format('d/m/Y');
            }

            return [
                'id' => $ba->no_ba . '|' . $ba->id_project . '|' . $ba->norut,
                'text' => $dropdownText,
                'no_ba' => $ba->no_ba,
                'id_project' => $ba->id_project,
                'norut' => $ba->norut,

                // Data dari History Proyek
                'cost_center' => $costCenter,
                'namaproject' => $namaProyek,
                'konsumen_nama' => $konsumenNama,
                'no_kontrak' => $ba->no_kontrak ?? '-',
                'nilai_proyek' => $nilaiProyek,
                'start_kontrak' => $ba->start_kontrak ?
                    \Carbon\Carbon::parse($ba->start_kontrak)->format('d/m/Y') : '-',
                'finish_kontrak' => $ba->finish_kontrak ?
                    \Carbon\Carbon::parse($ba->finish_kontrak)->format('d/m/Y') : '-',

                // Data dari Header RAB
                'mulai' => $mulai,
                'lama' => $lama,
                'akhir' => $akhir,

                // Data dari Berita Acara
                'periode_mulai' => $periodeBaMulai,
                'periode_akhir' => $periodeBAkhir,
                'nilai_ba' => $ba->nilai_ba
            ];
        });

        Log::info('Berita Acara data fetched', [
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
        $noBA = $request->get('no_ba');

        if (!$idProject || !$norut || !$noBA) {
            return response()->json([
                'success' => false,
                'message' => 'ID Project, Norut, dan No BA harus diisi'
            ], 400);
        }

        DB::enableQueryLog();

        // Get pendapatan data, ordered by created_at DESC (newest first)
        $pendapatans = PendapatanProyek::where('id_project', $idProject)
            ->where('norut', $norut)
            ->where('no_ba', $noBA)
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
            'no_ba' => $noBA,
            'query' => $queries,
            'count' => $pendapatans->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $pendapatans
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
            'no_ba' => 'required|string|max:9',
            'tanggal' => 'required|date',
            'no_dokumen' => 'required|string|max:100',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_pendapatan' => 'nullable|numeric|min:0',
            'file_ba' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        try {
            DB::beginTransaction();

            // Verify BA exists and is approved
            $beritaAcara = BeritaAcaraProject::where('id_project', $request->id_project)
                ->where('norut', $request->norut)
                ->where('no_ba', $request->no_ba)
                ->where('status', '03')
                ->first();

            if (!$beritaAcara) {
                return response()->json([
                    'success' => false,
                    'message' => 'Berita Acara tidak ditemukan atau belum disetujui'
                ], 404);
            }

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
                'no_ba' => $request->no_ba,
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
                'no_ba' => $request->no_ba,
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
            'no_ba' => 'required|string|max:9',
            'no_dokumen' => 'required|string|max:100',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after_or_equal:periode_mulai',
            'nilai_pendapatan' => 'nullable|numeric|min:0',
            'file_ba' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        try {
            DB::beginTransaction();

            // Find by composite key
            $pendapatan = PendapatanProyek::where('norut', $request->norut)
                ->where('id_project', $request->id_project)
                ->where('no_pendapatan', $noPendapatan)
                ->where('no_ba', $request->no_ba)
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
            $noBA = $request->get('no_ba');

            // Find by composite key
            $pendapatan = PendapatanProyek::where('norut', $norut)
                ->where('id_project', $idProject)
                ->where('no_pendapatan', $noPendapatan)
                ->where('no_ba', $noBA)
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
        $noBA = $request->get('no_ba');

        $pendapatan = PendapatanProyek::where('norut', $norut)
            ->where('id_project', $idProject)
            ->where('no_pendapatan', $noPendapatan)
            ->where('no_ba', $noBA)
            ->firstOrFail();

        if (!$pendapatan->file_ba || !Storage::disk('public')->exists($pendapatan->file_ba)) {
            abort(404, 'File tidak ditemukan');
        }

        $filePath = Storage::disk('public')->path($pendapatan->file_ba);
        $fileName = basename($pendapatan->file_ba);

        return response()->download($filePath, $fileName);
    }
}
