<?php

namespace App\Http\Controllers;

use App\Models\RABProyek;
use App\Models\Konsumen;
use App\Models\BidangJasa;
use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PencatatanPlenoRABController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar pengajuan RAB untuk pencatatan pleno
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');

            $query = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by progress
            if ($request->filled('progress')) {
                $query->where('progress', $request->get('progress'));
            }

            // Filter by hasil_pleno
            if ($request->filled('hasil_pleno')) {
                $query->where('hasil_pleno', $request->get('hasil_pleno'));
            }

            // Universal Search
            if ($request->filled('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('nopengajuan', 'like', "%{$search}%")
                      ->orWhere('cost_center', 'like', "%{$search}%")
                      ->orWhere('nama_project', 'like', "%{$search}%")
                      ->orWhere('dokumen_io', 'like', "%{$search}%")
                      ->orWhere('pm', 'like', "%{$search}%")
                      ->orWhereHas('konsumen', function($konsumenQuery) use ($search) {
                          $konsumenQuery->where('konsumen', 'like', "%{$search}%");
                      })
                      ->orWhereHas('masterDivisi', function($divisiQuery) use ($search) {
                          $divisiQuery->where('nama_divisi', 'like', "%{$search}%");
                      });
                });
            }

            $rabProyek = $query->orderBy('tgl_input', 'desc')
                               ->orderBy('nopengajuan', 'desc')
                               ->paginate($perPage);

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $rabProyek->items(),
                    'pagination' => [
                        'current_page' => $rabProyek->currentPage(),
                        'last_page' => $rabProyek->lastPage(),
                        'per_page' => $rabProyek->perPage(),
                        'total' => $rabProyek->total(),
                        'from' => $rabProyek->firstItem(),
                        'to' => $rabProyek->lastItem(),
                    ]
                ]);
            }

            return view('pencatatanpleno.index', compact('rabProyek'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * Form pencatatan hasil pleno RAB
     */
    public function edit(string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi', 'jenisProyek'])
                                  ->where('nopengajuan', $nopengajuan)
                                  ->firstOrFail();

            // Progress options
            $progressOptions = [
                '01' => 'Dokumen belum diterima',
                '02' => 'Proses tanda tangan BOD',
                '03' => 'Revisi RAB',
                '04' => 'Done',
            ];

            // Keterangan options
            $keteranganOptions = [
                'P' => 'Pleno',
                'T' => 'Tidak Pleno',
                'R' => 'Revisi RAB',
            ];

            // Hasil Pleno options
            $hasilPlenoOptions = [
                'TR' => 'Tercapai RKAP',
                'TT' => 'Tidak Tercapai RKAP',
            ];

            // Status options
            $statusOptions = [
                'D' => 'Draft RAB',
                'F' => 'Final RAB',
            ];

            return view('pencatatanpleno.edit', compact(
                'rabProyek',
                'progressOptions',
                'keteranganOptions',
                'hasilPlenoOptions',
                'statusOptions'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading pencatatan pleno form: ' . $e->getMessage());
            return redirect()->route('pencatatanpleno.index')
                           ->with('error', 'Data pengajuan RAB tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     * Menyimpan hasil pencatatan pleno RAB
     */
    public function update(Request $request, string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::where('nopengajuan', $nopengajuan)->firstOrFail();

            // Validation
            $validated = $request->validate([
                'progress' => 'nullable|string|max:2',
                'keterangan' => 'nullable|string|max:1',
                'hasil_pleno' => 'nullable|string|max:2',
                'margin_rkap' => 'nullable|numeric|min:0|max:100',
                'margin_pleno' => 'nullable|numeric|min:0|max:100',
                'catatan' => 'nullable|string|max:500',
                'status' => 'nullable|string|max:1',
                'hasil_upload' => 'nullable|file|mimes:xlsx,xls|max:10240',
            ], [
                'hasil_upload.mimes' => 'File harus berformat Excel (.xlsx, .xls)',
                'hasil_upload.max' => 'Ukuran file maksimal 10MB',
                'margin_rkap.numeric' => 'Margin RKAP harus berupa angka',
                'margin_pleno.numeric' => 'Margin Pleno harus berupa angka',
                'margin_rkap.max' => 'Margin RKAP maksimal 100%',
                'margin_pleno.max' => 'Margin Pleno maksimal 100%',
            ]);

            // Jika progress = Done (04), file upload wajib (kecuali sudah ada file sebelumnya)
            if ($request->progress === '04' && !$request->hasFile('hasil_upload') && empty($rabProyek->hasil_upload)) {
                return back()->withErrors(['hasil_upload' => 'Jika Progress = Done, dokumen RAB Final wajib diunggah'])
                            ->withInput();
            }

            // Update data
            $rabProyek->progress = $request->progress;
            $rabProyek->keterangan = $request->keterangan;
            $rabProyek->hasil_pleno = $request->hasil_pleno;
            $rabProyek->margin_rkap = $request->margin_rkap;
            $rabProyek->margin_pleno = $request->margin_pleno;
            $rabProyek->catatan = $request->catatan;
            $rabProyek->status = $request->status;

            // Handle file upload - Dokumen RAB Final
            if ($request->hasFile('hasil_upload')) {
                // Delete old file if exists
                if ($rabProyek->hasil_upload && Storage::disk('public')->exists($rabProyek->hasil_upload)) {
                    Storage::disk('public')->delete($rabProyek->hasil_upload);
                }

                $file = $request->file('hasil_upload');
                $fileName = 'RAB_Final_' . $nopengajuan . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengajuan_rab/hasil_pleno', $fileName, 'public');
                $rabProyek->hasil_upload = $path;
            }

            $rabProyek->save();

            return redirect()->route('pencatatanpleno.index')
                           ->with('success', 'Data pencatatan pleno RAB berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating pencatatan pleno: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     * Detail pengajuan RAB (read-only)
     */
    public function show(string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi', 'jenisProyek'])
                                  ->where('nopengajuan', $nopengajuan)
                                  ->firstOrFail();

            // Progress options for display
            $progressOptions = [
                '01' => 'Dokumen belum diterima',
                '02' => 'Proses tanda tangan BOD',
                '03' => 'Revisi RAB',
                '04' => 'Done',
            ];

            // Keterangan options
            $keteranganOptions = [
                'P' => 'Pleno',
                'T' => 'Tidak Pleno',
                'R' => 'Revisi RAB',
            ];

            // Hasil Pleno options
            $hasilPlenoOptions = [
                'TR' => 'Tercapai RKAP',
                'TT' => 'Tidak Tercapai RKAP',
            ];

            // Status options
            $statusOptions = [
                'D' => 'Draft RAB',
                'F' => 'Final RAB',
            ];

            return view('pencatatanpleno.show', compact(
                'rabProyek',
                'progressOptions',
                'keteranganOptions',
                'hasilPlenoOptions',
                'statusOptions'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading pencatatan pleno detail: ' . $e->getMessage());
            return redirect()->route('pencatatanpleno.index')
                           ->with('error', 'Data pengajuan RAB tidak ditemukan');
        }
    }
}
